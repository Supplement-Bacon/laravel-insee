<?php

namespace NSpehler\LaravelInsee\Console\Commands;

use Illuminate\Console\Command;
use NSpehler\LaravelInsee\Models\AuthorizationToken;

class PruneInseeAccessToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'insee:prune-tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune the expired access tokens';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Let\'s issue a brand new Insee access token and persist it securely');
        $this->newLine();

        AuthorizationToken::where('expires_at', '<', now())->delete();

        $this->info('Expired tokens pruned, well done ;)');
        return 0;
    }
}
