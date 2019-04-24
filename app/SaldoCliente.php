<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SaldoCliente extends Model
{
    protected $fillable = [
      "monto_asignado",
      "entrega_id",
      "user_id"
    ];

    public function entrega(){
      return $this->belongsTo("App\Entrega");
    }
}
