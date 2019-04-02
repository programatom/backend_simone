<?php

use Faker\Generator as Faker;

$factory->define(App\Producto::class, function (Faker $faker) {
    return [
      'nombre' => $faker->sentence,
      'precio' => rand (500, 3000),
      'descripcion' => $faker->paragraph,
    ];
});
