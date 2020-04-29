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
    protected $description = 'user delete';

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
        if (!$user = User::whereEmail($email)->first()) {
            $this->error('用户不存在！');
            return;
        }
        if ($user->delete()) {
            $this->info('用户删除成功！');
        }
    }
}
