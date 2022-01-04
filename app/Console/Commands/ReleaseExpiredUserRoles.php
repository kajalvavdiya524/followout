<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;

class ReleaseExpiredUserRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:release-expired-roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Release expired roles from users';

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
        $users = User::where('role_expires_at', '<', now())->get();

        foreach ($users as $user) {
            $this->line('Releasing ' . $user->role . ' role from user #' . $user->id . ' (' . $user->email . ')');
            $user->releaseExpiredRole();
        }
    }
}
