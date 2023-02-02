<?php

namespace NSpehler\LaravelInsee\Console\Commands;

use Illuminate\Console\Command;
use NSpehler\LaravelInsee\Insee;

class IssueInseeAccessToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'insee:issue-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Issue an Insee access token and store it';

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

        Insee::access_token(store:true);

        $this->info('New access token is stored, see you soon ...');
        return 0;
    }
}
