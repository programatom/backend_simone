<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePedidosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pedidos', function (Blueprint $table) {

          $table->increments('id');
          $table->integer("user_id");
          $table->integer("descuento");
          $table->string("periodicidad");
          $table->integer("repartidor_habitual_id");
          $table->integer("repartidor_excepcional_id");
          $table->string("estado");
          $table->string("dia_de_entrega");
          $table->string("forma_de_pago");
          $table->string("expiracion_descuento");

          $table->string("fecha_de_restauracion");
          $table->integer("dias_sin_procesar_danger");
          $table->integer("faltan_datos");
          $table->integer("alarma");
          $table->integer("danger");
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
        Schema::dropIfExists('pedidos');
    }
}
