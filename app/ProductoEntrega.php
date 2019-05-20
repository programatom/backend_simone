<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductoEntrega extends Model
{
    protected $fillable = [
      "cantidad",
      "entrega_id",
      "producto_id",
      "precio",
      "nombre",
      "fecha_de_entrega"

    ];

    public function entrega()
    {
     return $this->belongsTo('App\Entrega');
    }

}
