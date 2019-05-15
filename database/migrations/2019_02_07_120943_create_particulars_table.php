<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParticularsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('particulars', function (Blueprint $table) {
          $table->increments('id');
          $table->integer("user_id");
          $table->string("telefono")->nullable();
          $table->string("calle")->nullable();
          $table->string("numero")->nullable();
          $table->string("piso")->nullable();
          $table->string("depto")->nullable();
          $table->string("localidad")->nullable();
          $table->string("provincia")->nullable();
          $table->string("observaciones")->nullable();
          $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('particulars');
    }
}
