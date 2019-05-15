<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\User;

class Particular extends Model
{

    protected $fillable = [
      "user_id",
      "telefono",
      "calle",
      "numero",
      "piso",
      "depto",
      "localidad",
      "provincia",
      "observaciones",
    ];

    protected $attributes = [
      "user_id" => "",
      "telefono" => "",
      "calle" => "",
      "numero" => "",
      "piso" => "",
      "depto" => "",
      "localidad" => "",
      "provincia" => "",
      "observaciones" => "",
    ];

  public function user()
      {
         return $this->belongsTo(User::class);
      }

      public function pedido()
    {
       return $this->hasMany(Pedido::class, "user_id", "user_id");
    }
}
