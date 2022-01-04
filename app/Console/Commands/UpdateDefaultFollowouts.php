<?php

namespace App\Console\Commands;

use FollowoutHelper;
use App\User;
use Illuminate\Console\Command;

class UpdateDefaultFollowouts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'followouts:update-default';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update default followouts';

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
        // If followhost has no upcoming or ongoing public Followouts, make default Followout public
        $users = User::followhosts()
                        ->subscribed()
                        ->where('auto_show_default_followouts', '!=', false)
                        ->whereHas('followouts', function ($query) {
                            $query->default()->whereIn('privacy_type', ['followers', 'private']);
                        })
                        ->whereDoesntHave('followouts', function ($query) {
                            $query->default(false)->ongoingOrUpcoming()->public()->notReposted();
                        })
                        ->get();

        foreach ($users as $user) {
            FollowoutHelper::showDefaultFollowout($user->id);

            $this->line('Default Followout of user #'.$user->id.' ('.$user->email.') is now visible.');
        }
    }
}
