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
          $table->string('razon_social');
          $table->string('CUIT');
          $table->string('dom_fiscal');
          $table->string("telefono");
          $table->string("calle");
          $table->string("numero");
          $table->string("piso");
          $table->string("depto");
          $table->string("localidad");
          $table->string("provincia");
          $table->string("nombre_receptor");
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
        Schema::dropIfExists('empresas');
    }
}
