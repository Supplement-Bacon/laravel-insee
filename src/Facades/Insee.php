<?php

namespace NSpehler\LaravelInsee\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed siren(string $siren)
 * @method static mixed siret(string $siret)
 *
 * @see \NSpehler\LaravelInsee\InseeClient
 */
class Insee extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-insee';
    }
}
