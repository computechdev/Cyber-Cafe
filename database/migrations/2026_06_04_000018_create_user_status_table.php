<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserStatusTable extends Migration
{
    public function up()
    {
        Schema::create('user_status', function (Blueprint $table) {
            $table->increments('id_user_status');
            $table->string('status_txt', 50);
            $table->boolean('ativo')->default(true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_status');
    }
}
