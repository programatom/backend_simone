<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\User;
use App\Pedido;

class Empresa extends Model
{
  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function pedido()
  {
    return $this->hasMany(Pedido::class, "user_id", "user_id");
  }

    protected $fillable = [
      "razon_social",
      "CUIT",
      "dom_fiscal",
      "telefono",
      "calle",
      "numero",
      "piso",
      "depto",
      "localidad",
      "provincia",
      "nombre_receptor",
      "observaciones",
      "user_id",
    ];

    protected $attributes = array(
      "user_id" => 0,
      "razon_social" => "",
      "CUIT" => "",
      "dom_fiscal" => "",
      "telefono" => "",
      "calle" => "",
      "numero" => "",
      "piso" => "",
      "depto" => "",
      "nombre_receptor" => "",
      "provincia" => "",
      "localidad" => "",
      "observaciones" => ""
    );

}
