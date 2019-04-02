<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CuponUso;

class Cupon extends Model
{
    protected $fillable = [
      "codigo",
      "fecha_expiracion",
      "porcentaje_descuento",
      "tipo"
    ];


    public function usos(){
      return $this->hasMany(CuponUso::class);
    }
}
