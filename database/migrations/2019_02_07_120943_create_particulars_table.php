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
          $table->string("telefono");
          $table->string("calle");
          $table->string("numero");
          $table->string("piso");
          $table->string("depto");
          $table->string("localidad");
          $table->string("provincia");
          $table->string("observaciones");
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
