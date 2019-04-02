<?php

use Illuminate\Database\Seeder;

class ProductosSolicitadosTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      factory(App\ProductosSolicitado::class, 60)->create();
    }
}
