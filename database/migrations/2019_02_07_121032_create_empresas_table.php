<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmpresasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('empresas', function (Blueprint $table) {
          $table->increments('id');
          $table->integer('user_id');
          $table->string('razon_social')->nullable();
          $table->string('CUIT')->nullable();
          $table->string('dom_fiscal')->nullable();
          $table->string("telefono")->nullable();
          $table->string("calle")->nullable();
          $table->string("numero")->nullable();
          $table->string("piso")->nullable();
          $table->string("depto")->nullable();
          $table->string("localidad")->nullable();
          $table->string("provincia")->nullable();
          $table->string("nombre_receptor")->nullable();
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
        Schema::dropIfExists('empresas');
    }
}
