<?php

use Illuminate\Support\Str;
use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(App\User::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'email_verified_at' => now(),
        'password' => Hash::make("asdasd"), // secret
        'role' => "particular",
        'remember_token' => Str::random(10),
        'api_token' => Str::random(10),
        "saldo" => 0,
    ];
});

$factory->define(App\Empresa::class, function (Faker $faker) {
    static $number = 1;

    return [
      'razon_social' => rand (1,10000000),
      'CUIT' => rand (1,10000000),
      'dom_fiscal' => $faker->sentence,
      "telefono" => $faker->phoneNumber,
      "calle" => $faker->sentence,
      "numero" => rand (1,1000),
      "piso" => rand (1,20),
      "depto" => rand (1,10),
      "localidad" => $faker->sentence,
      "provincia" => $faker->sentence,
      "nombre_receptor" => $faker->name,
      "observaciones" => $faker->sentence,
      "user_id" => $number++
    ];
});

$factory->define(App\Particular::class, function (Faker $faker) {
  static $number = 0;
    return [
      "user_id" => $number++,
      "telefono" => $faker->phoneNumber,
      "calle" => $faker->sentence,
      "numero" => rand (1,20),
      "piso" => rand (1,20),
      "depto" => $faker->sentence,
      "localidad" => $faker->sentence,
      "provincia" => $faker->sentence,
      "observaciones" => $faker->sentence
    ];
});


/*
$factory->define(App\Entrega::class, function (Faker $faker) {
    return [
      "user_id" => rand (1,40),
      "pedido_id"=> rand (1,30),
      "estado" => ["demorada", "entregada" , "pausada", "pendiente"][rand(0,3)],
      "observaciones" => $faker->paragraph
    ];
});
*/

$factory->define(App\Pedido::class, function (Faker $faker) {
    return [
      "user_id" =>rand(1,40),
      "periodicidad" => ["semanal", "mensual" , "quincenal"][rand(0,2)],
      "repartidor_habitual" => "Tomas",
      "repartidor_excepcional" => ["Juan Carlos", "Tomas" , "Javier", "Sergio"][rand(0,3)],
      "estado" => "en proceso",
      "dia_de_entrega" => ["1" , "2", "3", "4", "5" , "6", "7"][rand(0,6)],
      "forma_de_pago" => ["efectivo", "cheque" , "transferencia", "mercado pago"][rand(0,3)],
      "descuento" => [10, 20 , 5, 15][rand(0,3)],
      "faltan_datos" => 0
    ];
});
