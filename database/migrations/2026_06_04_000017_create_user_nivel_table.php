<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserNivelTable extends Migration
{
    public function up()
    {
        Schema::create('user_nivel', function (Blueprint $table) {
            $table->increments('id_user_nivel');
            $table->string('nivel_user', 100);
            $table->string('nivel_sigla', 20);
            $table->boolean('ativo')->default(true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_nivel');
    }
}
