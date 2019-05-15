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
      "estado",
      "repartidor_habitual_id",
      "repartidor_excepcional_id",
      "dia_de_entrega",
      "descuento",
      "visto",
      "faltan_datos",
      "alarma",
      "danger",
      "dias_sin_procesar_danger",
      "expiracion_descuento",
      "fecha_de_restauracion",
      "date_change_alert"

    ];

    protected $attributes = [
      "forma_de_pago" => "",
      "estado" => "en proceso",
      "repartidor_habitual_id" => 0,
      "repartidor_excepcional_id" => 0,
      "dia_de_entrega"=> "",
      "descuento" => 0,
      "visto" => 0,
      "faltan_datos" => 1,
      "alarma" => 0,
      "danger" => 0,
      "dias_sin_procesar_danger" => 0,
      "expiracion_descuento" => 0,
      "fecha_de_restauracion" => "",
      "date_change_alert" => 0

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
