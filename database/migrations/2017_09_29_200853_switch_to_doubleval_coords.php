<?php

use Illuminate\Support\Facades\Schema;
use Jenssegers\Mongodb\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SwitchToDoublevalCoords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $users = \App\User::all();
        $followouts = \App\Followout::all();

        foreach ($users as $user) {
            if ($user->lat && $user->lng) {
                $lat = doubleval($user->lat);
                $lng = doubleval($user->lng);

                $user->lat = null;
                $user->lng = null;
                $user->save();

                $user->lat = $lat;
                $user->lng = $lng;
                $user->save();
            }
        }

        foreach ($followouts as $followout) {
            $lat = doubleval($followout->lat);
            $lng = doubleval($followout->lng);

            $followout->lat = null;
            $followout->lng = null;
            $followout->save();

            $followout->lat = $lat;
            $followout->lng = $lng;
            $followout->location = [
                $lng,
                $lat
            ];
            $followout->save();
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
