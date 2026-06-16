<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreditosTable extends Migration
{
    public function up()
    {
        Schema::create('creditos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('idprod', 4);
            $table->timestamp('data')->nullable();
            $table->integer('valor');
            $table->integer('status')->default(1);
            $table->integer('tipo');
        });
    }

    public function down()
    {
        Schema::dropIfExists('creditos');
    }
}
