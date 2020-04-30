<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class UserListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'code6:user-list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'User list';

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
        $users = User::all('email')->pluck('email')->toArray();
        if (empty($users)) {
            $this->error('User list is empty!');
        } else {
            $this->info(implode(PHP_EOL, $users));
        }
    }
}
