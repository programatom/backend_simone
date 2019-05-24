<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\cronEmisionEntregaController;


class entregaEmision extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:entregaEmision';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tarea diaria para emitir entregas';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
      $emision_controller = new cronEmisionEntregaController();
      $emision_controller->iniciar_proceso_cron();
    }
}
