<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfiguracaoTable extends Migration
{
    public function up()
    {
        Schema::create('configuracao', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamp('data_fechamento')->nullable();
            $table->string('usuario', 100);
            $table->integer('ligado')->default(1);
            $table->string('versao', 5);
        });
    }

    public function down()
    {
        Schema::dropIfExists('configuracao');
    }
}
