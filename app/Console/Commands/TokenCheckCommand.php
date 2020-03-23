<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TokenCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'code6:token-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check GitHub API access token';

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
     * @return mixed
     */
    public function handle()
    {
        //
    }
}
