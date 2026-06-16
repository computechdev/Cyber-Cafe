<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMetricaParcialTable extends Migration
{
    public function up()
    {
        Schema::create('metrica_parcial', function (Blueprint $table) {
            $table->increments('id_metrica_parcial');
            $table->integer('id_metrica_fk');
            $table->integer('id_cobranca');
            $table->integer('comissao_locacao');
            $table->integer('comissao_ponto');
            $table->integer('comissao_dono');
            $table->integer('acerto');
            $table->string('idprod', 4);
            $table->integer('saldo_total');
            $table->integer('entrada');
            $table->integer('saida');
            $table->integer('entrada_anterior')->default(0);
            $table->integer('saida_anterior')->default(0);
            $table->dateTime('data_zerou_parcial')->nullable();
            $table->boolean('status_leitura')->nullable()->default(true);
            $table->dateTime('data_fechamento_leitura')->nullable();
            $table->string('lote_fechamento', 150)->nullable();
            $table->integer('operador_fechamento')->nullable();
            $table->integer('cliente_fechamento')->nullable();
            $table->boolean('ativo')->default(true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('metrica_parcial');
    }
}
