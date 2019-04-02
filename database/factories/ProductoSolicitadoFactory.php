<?php

use Faker\Generator as Faker;

$factory->define(App\ProductosSolicitado::class, function (Faker $faker) {
    return [
      'producto_id' => rand (1,10),
      'cantidad' => rand (1,10),
      'pedido_id' => rand (1,30),
    ];
});
