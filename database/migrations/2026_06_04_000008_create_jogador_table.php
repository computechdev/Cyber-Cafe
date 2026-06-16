<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJogadorTable extends Migration
{
    public function up()
    {
        Schema::create('jogador', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_ponto')->nullable();
            $table->string('username')->nullable();
            $table->text('password')->nullable();
            $table->integer('creditos')->nullable();
            $table->string('idprod', 20)->nullable();
            $table->timestamp('data')->nullable()->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('jogador');
    }
}
