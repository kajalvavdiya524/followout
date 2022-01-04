<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGeolocationToVirtualFollowouts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \App\Followout::where('is_virtual', true)->chunk(250, function ($followouts) {
            foreach ($followouts as $followout) {
                $followout->lat = doubleval(40.7579747);
                $followout->lng = doubleval(-73.9855426);
                $followout->city = 'Manhattan';
                $followout->state = 'New York';
                $followout->address = 'Times Square';
                $followout->zip_code = '10036';
                $followout->location = FollowoutHelper::makeLocation($followout->lat, $followout->lng);
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
