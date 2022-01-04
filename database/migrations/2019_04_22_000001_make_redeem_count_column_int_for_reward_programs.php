<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeRedeemCountColumnIntForRewardPrograms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $rewardPrograms = \App\RewardProgram::all();

        foreach ($rewardPrograms as $rewardProgram) {
            $count = (int) $rewardProgram->redeem_count;

            $rewardProgram->redeem_count = null;
            $rewardProgram->save();

            $rewardProgram->redeem_count = $count;
            $rewardProgram->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
