<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            /*
             * Tabela padrão do Laravel + campos do sistema legado.
             *
             * A tabela legada se chamava `user`.
             * No Laravel, usaremos somente `users`.
             */
            $table->id();

            // Identificação do usuário legado
            $table->unsignedInteger('legacy_user_id')->nullable();

            // Campos padrão do Laravel
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();

            // Campos vindos da tabela legada `user`
            $table->string('username', 25)->nullable();
            $table->string('legacy_passwd', 40)->nullable();
            $table->unsignedInteger('nivel')->default(3);
            $table->dateTime('cadastro_legado')->nullable();
            $table->integer('id_apoio')->default(0);
            $table->integer('porcentagem')->default(0);
            $table->integer('id_pais')->default(0);
            $table->timestamp('data_corte')->nullable();
            $table->integer('validade')->default(0);
            $table->integer('revalidar')->default(0);
            $table->date('data_validacao')->nullable();
            $table->integer('status')->default(1);
            $table->integer('afiliado')->default(0)->comment('0 - Não, 1 - Sim');
            $table->integer('fechar_faturas_ponto')->nullable()->default(0)->comment('1=sim, 0=não');

            //   //Adicionado
            // $table->string('cpf', 15);
            // $table->string('chave_pix', 255);

            $table->timestamps();

            $table->index('legacy_user_id');
            $table->index('username');
            $table->index('nivel');
            $table->index('id_apoio');
            $table->index('status');
          
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
