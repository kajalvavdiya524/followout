<?php

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPromoCodesToSalesRepresentatives extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $reps = \App\SalesRepresentative::all();

        foreach ($reps as $rep) {
            $userIds = $rep->user_ids ?? [];

            foreach ($userIds as $userId) {
                $user = \App\User::find($userId);

                if ($user) {
                    $user->sales_rep_code = $rep->code;
                    $user->sales_rep_promo_code = null;
                    $user->save();
                }
            }

            $rep->promo_code = $rep->code . '-PROMO-' . mb_strtoupper(Str::random(10));
            $rep->user_ids = null;
            $rep->save();
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
