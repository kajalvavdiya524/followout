<?php

use Illuminate\Support\Facades\Schema;
use Jenssegers\Mongodb\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRecursiveFollowoutReposts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $followouts = \App\Followout::reposted()->get();

        foreach ($followouts as $followout) {
            $followout->top_parent_followout_id = $followout->parent_followout_id;
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
