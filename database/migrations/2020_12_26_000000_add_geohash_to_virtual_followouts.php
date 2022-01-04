<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGeohashToVirtualFollowouts extends Migration
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
                // Add geohash for 40.7579747,-73.9855426 (Times Square)
                $followout->geohash = 'dr5ru7tj54g';
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
