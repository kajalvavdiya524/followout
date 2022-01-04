<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;

class ClearExpiredTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tokens:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete expired tokens';

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
        $this->clearPasswordResetTokens();
    }

    public function clearPasswordResetTokens()
    {
        $users = User::where('password_reset_token_expires_at', '<', now())->get();

        foreach ($users as $user) {
            $user->password_reset_token = null;
            $user->password_reset_token_expires_at = null;
            $user->save();
        }
    }
}
