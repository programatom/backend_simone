<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MuestrasPedido extends Model
{
    protected $fillable = [
      "nombre",
      "email",
      "barrio_localidad",
      "telefono",
      "mensaje",
      "visto",
    ];

    protected $attributes = [
      "visto" => 0
    ];

}
