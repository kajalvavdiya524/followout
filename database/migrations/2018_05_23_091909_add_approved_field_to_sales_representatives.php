<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddApprovedFieldToSalesRepresentatives extends Migration
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
            $rep->first_name = $rep->name;
            $rep->last_name = '';
            $rep->email = '';
            $rep->hash = null;
            $rep->accepted = true;
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
