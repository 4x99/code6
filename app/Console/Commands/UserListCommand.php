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
    protected $description = 'user list';

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
        $users = User::all();
        $str = ' id | email | created_at | updated_at '.PHP_EOL;
        foreach ($users as $user) {
            $str .= " {$user->id} | {$user->email} | {$user->created_at} | {$user->updated_at} ".PHP_EOL;
        }
        $this->info($str);
    }
}
