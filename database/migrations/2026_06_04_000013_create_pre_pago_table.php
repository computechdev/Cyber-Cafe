<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrePagoTable extends Migration
{
    public function up()
    {
        Schema::create('pre_pago', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_apoio');
            $table->string('codigo', 20);
            $table->integer('valor');
            $table->boolean('status')->default(true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('pre_pago');
    }
}
