<?php

use Illuminate\Database\Seeder;

class ParticularsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      factory(App\Particular::class, 40)->create();
    }
}
