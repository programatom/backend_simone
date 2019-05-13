<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Image;
use App\Pedido;

class Producto extends Model
{

  protected $fillable = [
    "precio",
    "nombre",
    "descripcion",
    "posicion"
  ];

  protected $attributes = [
    "posicion" => 1
  ];

      public function pedidos()
    {
       return $this->belongsToMany(Pedido::class, 'productos_solicitados', 'producto_id', 'pedido_id');
    }

}
