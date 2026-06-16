<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransacoesTable extends Migration
{
    public function up()
    {
        Schema::create('transacoes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('tipo')->comment('1=Entrada; 2=Saida');
            $table->integer('valor');
            $table->timestamp('data_hora')->nullable();
            $table->string('idprod', 4);
        });
    }

    public function down()
    {
        Schema::dropIfExists('transacoes');
    }
}
