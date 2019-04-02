<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExceptionProduct extends Model
{
    protected $fillable = [
      "producto_id",
      "entrega_id",
      "pedido_id",
      "cantidad",
    ];
  
}
