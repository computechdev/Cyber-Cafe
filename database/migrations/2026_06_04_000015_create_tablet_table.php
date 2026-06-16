<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTabletTable extends Migration
{
    public function up()
    {
        Schema::create('tablet', function (Blueprint $table) {
            $table->increments('id');
            $table->string('idprod', 4);
            $table->string('cliente', 64);
            $table->timestamp('cadastro')->nullable();
            $table->integer('validade');
            $table->integer('status');
            $table->integer('ligado')->default(1);
            $table->integer('zerar')->default(0);
            $table->integer('destrava')->default(0)->comment('0 Tavado - 1 Destravado');
            $table->string('expiracao', 16);
            $table->integer('id_ponto')->nullable()->default(0);
            $table->integer('id_apoio');
            $table->integer('dificuldade')->default(50);
            $table->integer('pendrive_id')->default(1);
            $table->integer('antecipado')->default(0);
            $table->boolean('creditoteclado')->default(false);
            $table->integer('centavosbingo')->default(1);
            $table->integer('apostamaxhalloween')->default(10);
            $table->boolean('leituraonlinesincronizada')->default(true);
            $table->boolean('zerarleituraparcial')->default(false);
            $table->boolean('zerarleituraoficialbackup')->default(false);
            $table->decimal('acum1min', 11, 2)->default(200.00);
            $table->decimal('acum2min', 11, 2)->default(300.00);
            $table->decimal('acum3min', 11, 2)->default(400.00);
            $table->decimal('acum1med', 11, 2)->default(200.00);
            $table->decimal('acum2med', 11, 2)->default(300.00);
            $table->decimal('acum3med', 11, 2)->default(400.00);
            $table->decimal('acum1max', 11, 2)->default(500.00);
            $table->decimal('acum2max', 11, 2)->default(600.00);
            $table->decimal('acum3max', 11, 2)->default(700.00);
            $table->decimal('acum4min', 11, 2)->default(100.00);
            $table->decimal('acum4med', 11, 2)->default(100.00);
            $table->decimal('acum4max', 11, 2)->default(200.00);
            $table->decimal('acum5min', 11, 2)->default(100.00);
            $table->decimal('acum5med', 11, 2)->default(100.00);
            $table->decimal('acum5max', 11, 2)->default(100.00);
            $table->decimal('acum6min', 11, 2)->default(100.00);
            $table->decimal('acum6med', 11, 2)->default(100.00);
            $table->decimal('acum6max', 11, 2)->default(100.00);
            $table->double('porcent_acumu')->default(3);
            $table->integer('lastversion')->default(1);
            $table->boolean('ativo')->default(true);
            $table->boolean('zerarleituravirtual')->default(false);
            // $table->boolean('zerarleituravirtual');
            $table->boolean('matematica_slot')->default(false);
            $table->boolean('modo_slot')->default(true);
            // $table->dateTime('ultimo_contato');
            $table->dateTime('ultimo_contato')->nullable();

            //Adicionado

            $table->decimal('acumSenaMin', 11, 2)->default(20.00);
            $table->decimal('acumSenaAtu', 11, 2)->default(20.00);
            $table->decimal('acumSenaMax', 11, 2)->default(20.00);

            $table->decimal('acumBombaMin', 11, 2)->default(20.00);
            $table->decimal('acumBombaAtu', 11, 2)->default(40.00);
            $table->decimal('acumBombaMax', 11, 2)->default(60.00);

            $table->integer('retencao_slots')->default(1);
            $table->integer('dificul_bonus')->default(160000);

            $table->float('taxa_maxima')->default(0.55);
            $table->float('acrecimo_taxa_max')->default(0.03);

            $table->integer('periodo_reducao')->default(10000);
            $table->integer('extras_min_rotina')->default(30);
            $table->integer('saldo_para_acumulao')->default(100000);
            $table->integer('premio_do_acumulado')->default(200);
            $table->integer('tam_premio_max')->default(3);

            $table->integer('saldo_min_bingo_g1')->default(35000);
            $table->integer('saldo_min_bingo_g2')->default(50000);
            $table->integer('saldo_min_bingo_g3')->default(100000);

            $table->integer('qtn_jogos_azar')->default(8);
            $table->float('probabilidada_premios_azar')->default(0.25);
            $table->float('taxa_max_chance_azar')->default(0);

            $table->integer('qtn_jogos_normal')->default(4);
            $table->float('probabilidada_premios_normal')->default(0.5);
            $table->float('taxa_max_chance_normal')->default(0.03);

            $table->integer('qtn_jogos_neutro')->default(3);
            $table->float('probabilidade_premios_neutro')->default(0.25);
            $table->float('taxa_max_chance_neutro')->default(0);

            $table->integer('qtn_jogos_sorte')->default(5);
            $table->float('probabilidade_premios_sorte')->default(0.5);
            $table->float('taxa_max_chance_sorte')->default(0.04);

            $table->integer('qtn_jogos_moderado')->default(8);
            $table->float('probabilidade_premios_moderado')->default(0.5);
            $table->float('taxa_max_chance_moderado')->default(0.01);

            $table->float('limites_nivel_show_n2')->default(0.4);
            $table->float('limites_nivel_show_n3')->default(0.2);
            $table->float('limites_nivel_show_n4')->default(0.2);

            $table->string('status_bonus', 5)->default('0');

            $table->decimal('valor', 10, 0)->default(0);

            $table->integer('kiosk_sis')->default(0);
            $table->integer('tipo_tela')->default(1);
            $table->integer('tipo_inter')->default(0);

            $table->string('senha', 4)->default('8520');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tablet');
    }
}
