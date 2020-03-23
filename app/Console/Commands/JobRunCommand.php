<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class JobRunCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'code6:job-run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Take jobs from the queue and execute them';

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
