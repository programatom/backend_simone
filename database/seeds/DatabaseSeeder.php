<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
      $this->call(UsersTableSeeder::class);
      $this->call(EmpresasSeeder::class);
      $this->call(ParticularsSeeder::class);
      $this->call(PedidosSeeder::class);
      //$this->call(EntregasSeeder::class);
      $this->call(ProductosSolicitadosTableSeeder::class);
      $this->call(ProductosTableSeeder::class);


    }
}
