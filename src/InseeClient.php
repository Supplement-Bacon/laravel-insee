<?php

namespace NSpehler\LaravelInsee;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request as Psr7Request;
use GuzzleHttp\Psr7\Response as Psr7Response;

class InseeClient
{
    const API_URL = "https://api.insee.fr";

    const ENDPOINT_TOKEN = "/token";
    const ENDPOINT_SIRENE_V3 = "/entreprises/sirene/V3";

    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * @var array
     */
    protected $headers;

    /**
     * @var array
     */
    public $additionalData = [];

    /**
     * @var int
     */
    public $maxRetries = 2;

    /**
     * @var int
     */
    public $retryDelay = 500;

    /**
     * @param int $guzzleClientTimeout
     */
    public function __construct($guzzleClientTimeout = 0)
    {
        $this->client = new Client([
            'handler' => $this->createGuzzleHandler(),
            'timeout' => $guzzleClientTimeout,
        ]);
        $this->headers = ['headers' => [
            'Content-Type' => 'application/json; charset=utf-8',
        ]];
    }

    /**
     * Request access token from Insee
     */
    public function access_token()
    {
        // Base64 encode consumer key and secret
        $token = base64_encode(config('insee.consumer_key') . ':' . config('insee.consumer_secret'));

        $this->headers = [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => 'Basic ' . $token,
            ],
            'form_params' => [
                'grant_type' => 'client_credentials',
            ],
        ];

        $result = $this->post(self::ENDPOINT_TOKEN);
        // dd($result);

        $result = json_decode($result->getBody());

        return $result->access_token;
    }

    public function siren($siren)
    {
        // Format number
        $siren = str_replace(' ', '', $siren);

        $this->requiresAuth();

        $result = $this->get(self::ENDPOINT_SIRENE_V3 . '/siren/' . $siren);

        return json_decode($result->getBody());
    }

    public function siret($siret)
    {
        // Format number
        $siret = str_replace(' ', '', $siret);

        $this->requiresAuth();

        $result = $this->get(self::ENDPOINT_SIRENE_V3 . '/siret/' . $siret);

        return json_decode($result->getBody());
    }

    private function requiresAuth()
    {

        if (config('insee.store_token')) {
            // TODO: Fetch token from DB
        }

        // Generate a new token
        $this->headers['headers']['Authorization'] = 'Bearer ' . $this->access_token();
    }

    /**
     * HTTP Client request methods
     */

    private function get(string $endPoint, array $queryParameters = [])
    {
        return $this->client->get(
            self::API_URL . $endPoint . $this->prepareQueryParameters($queryParameters),
            $this->headers
        );
    }

    private function post($endPoint)
    {
        return $this->client->post(
            self::API_URL . $endPoint . $this->prepareQueryParameters(),
            $this->headers
        );
    }

    private function prepareQueryParameters(array $data = []): string
    {
        return $data || $this->additionalData
        ? '?' . http_build_query(array_merge($data, $this->additionalData))
        : '';
    }

    /**
     * Guzzle Handler
     */
    private function createGuzzleHandler()
    {
        return tap(HandlerStack::create(new CurlHandler()), function (HandlerStack $handlerStack) {
            $handlerStack->push(Middleware::retry(function ($retries, Psr7Request $request, Psr7Response $response = null, RequestException $exception = null) {
                if ($retries >= $this->maxRetries) {
                    return false;
                }

                if ($exception instanceof ConnectException) {
                    return true;
                }

                if ($response && $response->getStatusCode() >= 500) {
                    return true;
                }

                return false;
            }), $this->retryDelay);
        });
    }
}
