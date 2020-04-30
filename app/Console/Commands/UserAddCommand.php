<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

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
    protected $description = 'user add';

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
        $email = $this->argument('email');
        $password = $this->argument('password');
        if (User::whereEmail($email)->exists()) {
            $this->error('User already exists！');
            return;
        }
        $data = [
            'email' => $email,
            'password' => bcrypt($password),
        ];
        if (User::create($data)) {
            $this->info('User add success！');
        }
    }
}
