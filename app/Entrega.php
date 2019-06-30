<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\User;
use App\Pedido;


class Entrega extends Model
{
      public function user()
    {
       return $this->belongsTo('App\User');
    }

        public function pedido()
     {
         return $this->belongsTo('App\Pedido');
     }

     public function productos_entregados(){
       return $this->hasMany('App\ProductoEntrega');
     }

     protected $casts = [ 
      'user_id' => 'integer', 
      'pedido_id' => 'integer',
      'derivada' => "integer",
      "adelanta" => "integer",
      "reintentar" => "integer",
      "out_of_schedule" => "integer",
      "paga_con" => "integer",
      "entregas_adelantadas" => "integer"
      ];

     protected $fillable = [
       "user_id",
       "pedido_id",
       "fecha_de_entrega_potencial",
       "fecha_de_procesamiento_real",
       "derivada",
       "estado",
       "observaciones",
       "paga_con",
       "adelanta",
       "entregas_adelantadas",
       "reintentar",
       "out_of_schedule"


     ];

     protected $attributes = [
       "derivada" => 0,
       "fecha_de_procesamiento_real" => "",
       "observaciones" => "",
       "estado" => "sin procesar",
       "paga_con" => 0,
       "entregas_adelantadas"=> 0,
       "adelanta" => 0,
       "reintentar" => 0,
       "out_of_schedule" => 0
     ];

}
