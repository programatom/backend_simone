<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEntregasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entregas', function (Blueprint $table) {
          $table->increments('id');
          $table->integer("user_id");
          $table->integer("pedido_id");

          $table->string("fecha_de_entrega_potencial");
          $table->string("fecha_de_procesamiento_real");
          $table->integer("paga_con");
          $table->integer("adelanta");
          $table->integer("entregas_adelantadas");
          $table->integer("reintentar");
          $table->string("estado");
          $table->text("observaciones");
          $table->integer("derivada");

          $table->integer("out_of_schedule");
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
        Schema::dropIfExists('entregas');
    }
}
