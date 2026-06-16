<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRegiaoTable extends Migration
{
    public function up()
    {
        Schema::create('regiao', function (Blueprint $table) {
            $table->unsignedTinyInteger('id_pais');
            $table->string('nome_pais', 50);
            $table->string('name_pais', 50);
            $table->char('moeda_sigla', 3)->nullable();
            $table->boolean('status')->default(true);
            $table->string('timezone', 150)->nullable();

            // No SQL legado esta tabela não tinha PRIMARY KEY definida.
            // Mantive sem primary key para ficar fiel ao dump.
        });
    }

    public function down()
    {
        Schema::dropIfExists('regiao');
    }
}
