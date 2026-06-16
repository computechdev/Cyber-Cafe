<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCrashlogTable extends Migration
{
    public function up()
    {
        Schema::create('crashlog', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTime('datetime')->nullable();
            $table->string('idprod', 4)->nullable();
            $table->string('version', 7)->nullable();
            $table->string('platform', 100)->nullable();
            $table->mediumText('text')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('crashlog');
    }
}
