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

    public function imagenes()
  {
     return $this->belongsToMany(Image::class , "elemento_con_images", "producto_id", "imagen_id")->withPivot('rol');
  }
}
