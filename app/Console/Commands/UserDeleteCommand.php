<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class UserDeleteCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'code6:user-delete {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'User delete';

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
     * @return void
     */
    public function handle()
    {
        $email = $this->argument('email');
        if (User::where('email', $email)->delete()) {
            $this->info('User delete success!');
        } else {
            $this->error('User delete fail, may not exist!');
        }
    }
}
