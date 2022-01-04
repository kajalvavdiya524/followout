<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRedeemedAtColumnToRewardProgramJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $jobs = \App\RewardProgramJob::redeemed()->get();

        foreach ($jobs as $job) {
            if (is_null($job->redeemed_at)) {
                $job->redeemed_at = now();
                $job->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reward_program_jobs');
    }
}
