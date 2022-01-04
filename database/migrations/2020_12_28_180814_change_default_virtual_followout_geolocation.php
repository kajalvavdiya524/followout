<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeDefaultVirtualFollowoutGeolocation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \App\Followout::where('is_virtual', true)->where('lat', 40.7579747)->where('lng', -73.9855426)->chunk(250, function ($followouts) {
            foreach ($followouts as $followout) {
                $followout->city = 'İskilip';
                $followout->state = 'Çorum';
                $followout->address = 'Beyoğlan';
                $followout->zip_code = '19400';
                $followout->lat = doubleval(40.866667);
                $followout->lng = doubleval(34.566667);
                $followout->geohash = 'sz0yew3q8c1';
                $followout->save();
            }
        });
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
