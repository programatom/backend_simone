<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMuestrasPedidosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('muestras_pedidos', function (Blueprint $table) {
            $table->increments('id');
            $table->string("nombre");
            $table->string("email");
            $table->string("barrio_localidad");
            $table->integer("telefono");
            $table->string("mensaje");
            $table->integer("visto");
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
        Schema::dropIfExists('muestras_pedidos');
    }
}
