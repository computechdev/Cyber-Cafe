<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAcumuladosPagosTable extends Migration
{
    public function up()
    {
        Schema::create('acumulados_pagos', function (Blueprint $table) {
            $table->increments('id_acumulados_pagos');
            $table->string('idTablet', 50);
            $table->dateTime('data_pago');
            $table->integer('valor_pago');
            $table->tinyInteger('ativo')->default(1);
        });
    }

    public function down()
    {
        Schema::dropIfExists('acumulados_pagos');
    }
}
