<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeituraTable extends Migration
{
    public function up()
    {
        Schema::create('leitura', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_metrica')->nullable();
            $table->string('idprod', 4);
            $table->string('creditos', 12);
            $table->unsignedInteger('entrada')->default(0);
            $table->unsignedInteger('saida')->default(0);
            $table->unsignedInteger('entrada_virtual')->default(0);
            $table->unsignedInteger('saida_virtual')->default(0);
            $table->unsignedInteger('apostado');
            $table->unsignedInteger('premiado');
            $table->integer('status');
            $table->string('data', 16);
            $table->timestamp('dataorder')->nullable();
            $table->boolean('ativo')->default(true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('leitura');
    }
}
