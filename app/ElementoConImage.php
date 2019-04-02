<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ElementoConImage extends Model
{
  protected $fillable = [
    "producto_id",
    "imagen_id",
    "rol",
  ];
}
