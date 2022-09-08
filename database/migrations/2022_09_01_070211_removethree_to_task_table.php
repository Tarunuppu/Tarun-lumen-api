<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemovethreeToTaskTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('tasks')->update(['duedate' => '2022-08-01 00:00:00']);
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('assignee')->change();
            $table->string('createdby')->change();
            $table->datetime('duedate')->change()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            //
        });
    }
}
