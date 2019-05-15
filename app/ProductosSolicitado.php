<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductosSolicitado extends Model
{
  protected $fillable = [
    "producto_id",
    "pedido_id",
    "cantidad"
  ];

  // TEST DANGER

  protected $attributes = [
    "cantidad" => 0
  ];

}
