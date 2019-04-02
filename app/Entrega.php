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

     protected $fillable = [
       "user_id",
       "pedido_id",
       "fecha_de_entrega_potencial",
       "fecha_de_procesamiento_real",
       "derivada",
       "estado",
       "observaciones",
       "exception_product",
       "paga_con"
     ];

     protected $attributes = [
       "derivada" => 0,
       "fecha_de_procesamiento_real" => "",
       "observaciones" => "",
       "estado" => "sin procesar",
       "exception_product" => 0,
       "paga_con" => 0,

     ];

}
