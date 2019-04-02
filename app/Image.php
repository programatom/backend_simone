<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class Image extends Model
{
  protected $fillable = [
    "url",
    "nombre"
  ];
  public function producto(){


    return $this->belongsToMany("App\Product", "elemento_con_images","imagen_id","producto_id");
  }

}
