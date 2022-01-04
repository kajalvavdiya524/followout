<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;

class MakeAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make user an admin';

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
        $userID = $this->ask('What is the ID of user that will become admin?');

        $user = User::find($userID);

        if (is_null($user)) {
            return $this->error('User not found.');
        }

        $user->role = 'admin';
        $user->save();

        $this->line($user->name . ' is now an admin.');
    }
}
