<?php

use Illuminate\Support\Facades\Schema;
use Jenssegers\Mongodb\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenamePicturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $db = config('database.connections.mongodb.database');

        DB::connection('mongodb')->getMongoClient()->admin->command([
            'renameCollection' => "{$db}.pictures",
            'to' => "{$db}.files",
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $db = config('database.connections.mongodb.database');

        DB::connection('mongodb')->getMongoClient()->admin->command([
            'renameCollection' => "{$db}.files",
            'to' => "{$db}.pictures",
        ]);
    }
}
