<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class UserAddCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'code6:user-add {email} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'User add';

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
        $password = $this->argument('password');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('User add fail, email format is not correct!');
            return;
        }

        $user = User::firstOrCreate(['email' => $email], ['password' => Hash::make($password)]);
        if ($user->wasRecentlyCreated) {
            $this->info('User add success!');
        } else {
            $this->error('User add fail, may already exist!');
        }
    }
}
