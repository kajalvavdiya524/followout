<?php

namespace App\Console\Commands;

use App\Payout;
use Illuminate\Console\Command;

class UpdatePayoutsData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payouts:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Payouts data';

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
        $payouts = Payout::getUnresolved();

        $this->line('Updating payouts data...');

        foreach ($payouts as $payout) {
            $this->line('Updating data for payout #'.$payout->id);
            $payout->updatePayoutData();
        }
    }
}
