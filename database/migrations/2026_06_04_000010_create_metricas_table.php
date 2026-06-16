<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMetricasTable extends Migration
{
    public function up()
    {
        Schema::create('metricas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_cobranca')->nullable();
            $table->integer('comissao_locacao');
            $table->integer('comissao_ponto');
            $table->integer('comissao_dono');
            $table->integer('acerto');
            $table->string('idprod', 4);
            $table->timestamp('dataorder')->nullable();
            $table->integer('saldo_total');
            $table->integer('entrada');
            $table->integer('saida');
            $table->integer('entrada_anterior')->default(0);
            $table->integer('saida_anterior')->default(0);
            $table->integer('status')->default(1)->comment('1 - Aberta 0 - Fechada');
            $table->integer('status_leitura')->default(1);
            $table->dateTime('data_fechamento_leitura')->nullable();
            $table->string('lote_fechamento', 150)->nullable();
            $table->integer('operador_fechamento')->nullable();
            $table->integer('cliente_fechamento')->nullable();
            $table->boolean('disponivel_painel_admin')->nullable()->default(true)->comment('1=mostrar os valores no painel administrativo do admin; 0= não mostrar');
            $table->boolean('ativo')->default(true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('metricas');
    }
}
