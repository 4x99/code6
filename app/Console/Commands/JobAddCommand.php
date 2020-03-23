<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class JobAddCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'code6:job-add';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add scan job to the queue';

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
