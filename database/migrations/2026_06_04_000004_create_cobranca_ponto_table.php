<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCobrancaPontoTable extends Migration
{
    public function up()
    {
        Schema::create('cobranca_ponto', function (Blueprint $table) {
            $table->increments('id_cobranca');
            $table->integer('id_ponto');
            $table->integer('id_cobranca_locacao')->nullable();
            $table->timestamp('data_processamento')->nullable();
            $table->date('periodo_prestacao')->nullable();
            $table->string('tipo_cobranca', 50)->nullable();
            $table->date('data_vencimento')->nullable();
            $table->integer('valor_total')->default(0);
            $table->boolean('pago')->default(false);
            $table->date('data_pagamento')->nullable();
            $table->integer('cobranca_fechada')->default(0);
            $table->integer('cliente_fechamento')->nullable();
            $table->integer('operador_fechamento')->nullable();
            $table->boolean('ativo')->default(true);
            $table->text('lote_fechamento_id_cobrancas')->nullable();
            $table->char('lote_fechamento_hash', 50)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cobranca_ponto');
    }
}
