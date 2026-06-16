<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePontoTable extends Migration
{
    public function up()
    {
        Schema::create('ponto', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome', 100);
            $table->integer('porcent_ponto')->default(0);
            $table->timestamp('cadastro')->nullable();
            $table->integer('id_apoio');
            $table->boolean('status')->default(true);
            $table->string('passwd', 10)->nullable();
            $table->boolean('modo')->default(false);
        });
    }

    public function down()
    {
        Schema::dropIfExists('ponto');
    }
}
