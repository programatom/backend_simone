<?php

use Illuminate\Database\Seeder;

class EntregasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      factory(App\Entrega::class, 200)->create();
    }
}
