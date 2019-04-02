<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductosSolicitado extends Model
{
  protected $fillable = [
    "producto_id",
    "cantidad",
    "pedido_id",
  ];

}
