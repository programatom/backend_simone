<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Entrega;
use App\User;
use App\Producto;

class Pedido extends Model
{

    protected $fillable = [
      "user_id",
      "periodicidad",
      "forma_de_pago",
      "monto_sin_desc",
      "monto_con_desc",
      "estado",
      "repartidor_habitual",
      "repartidor_excepcional",
      "dia_de_entrega",
      "descuento",
      "visto",
      "estado_emision",
      "faltan_datos",
      "veces_por_semana",
      "alarma",
      "danger",
      "dias_sin_procesar_danger",
    ];

    protected $attributes = [
      "forma_de_pago" => "",
      "estado" => "en proceso",
      "repartidor_habitual" => "",
      "repartidor_excepcional" => "",
      "dia_de_entrega"=> "",
      "descuento" => 0,
      "visto" => 0,
      "estado_emision" => 0,
      "faltan_datos" => 1,
      "veces_por_semana" => 1,
      "alarma" => 0,
      "danger" => 0,
      "dias_sin_procesar_danger" => 0,
    ];



      public function user()
    {
       return $this->belongsTo(User::class, "user_id");
    }

    public function entregas(){

      return $this->hasMany(Entrega::class, 'pedido_id');

    }

    public function productos(){

      return $this->belongsToMany(Producto::class, "productos_solicitados" ,'pedido_id', "producto_id");

    }

}
