<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;


use App\Particular;
use App\Empresa;
use App\Empleado;

use App\Entrega;
use App\Pedido;
use App\CuponUso;


class User extends Authenticatable
{
    use Notifiable;

    public function particular()
    {
      return $this->hasOne(Particular::class, 'user_id');
    }

    public function empresa()
    {
      return $this->hasOne(Empresa::class, 'user_id');
    }

    public function empleado()
    {
      return $this->hasOne(Empleado::class, 'user_id');
    }

    public function pedidos(){
      return $this->hasMany(Pedido::class);
    }

    public function entregas(){
      return $this->hasMany(Entrega::class);
    }

    public function usos_cupon(){
      return $this->hasMany(CuponUso::class);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'role', "saldo"
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $attributes = [
        "saldo" => 0
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function generateToken()
      {
          $this->api_token = str_random(60);
          $this->save();

          return $this->api_token;
      }

}
