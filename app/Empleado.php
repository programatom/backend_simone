<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\User;
use App\Pedido;

class Empleado extends Model
{
  protected $fillable = [
    "nombre",
    "user_id",
    "dni"
  ];

  protected $attributes = array(
    "nombre"=> "",
    "dni"=> ""
  );

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function pedidos_habituales()
  {
    return $this->hasMany(Pedido::class, 'repartidor_habitual', 'nombre');
  }

  public function pedidos_excepcionales()
  {
    return $this->hasMany(Pedido::class, 'repartidor_excepcional', 'nombre');
  }
}
