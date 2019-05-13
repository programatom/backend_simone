<?php

namespace App\Http\Controllers;
use App\Http\Controllers\cronEmisionEntregaController;
use App\Http\Controllers\EntregaController;
use App\Entrega;
use App\Pedido;

use DateTime;

class TestController extends Controller
{

  public function simulate ()
  {
    $hoy = date("Y/m/d");
    $collect_test_data = array();
    for ($i = 1; $i <= 50; $i++) {
        $cron = new cronEmisionEntregaController();
        if($i != 1){
          $hoy = date('Y/m/d', strtotime($hoy.' + 1 day'));
        }
        $cron->iniciar_proceso_cron($hoy);

        // Ahora todas las entregas de hoy debería procesarlas
        $test_data = $this->procesar_entregas_de_hoy($hoy);
        $collect_test_data[] = $test_data;
    }
    $hoy = date('Y/m/d', strtotime($hoy.' + 1 day'));
    $entregas = Entrega::all();
    dd($collect_test_data);
  }

  public function procesar_entregas_de_hoy($hoy){

    $entregas_hoy = Entrega::where("fecha_de_entrega_potencial", $hoy)->get();
    $test_data = array();
    foreach($entregas_hoy as $entrega){
      $random = rand( 0, 100);
      $test_data[] = $random;
      if($random < 3){
        $this->cancelada($entrega, $hoy);
      } else if ($random >= 3 && $random < 10) {
        $this->entregada($entrega, $hoy);
      } else if($random >= 10 && $random < 83){
        $this->entregada($entrega, $hoy);
      } else if ($random >= 83 && $random <= 100){
        // No se hace nada con la entrega
      }
    }
    return $test_data;
  }

  public function cancelada($entrega, $hoy){
    $entrega->estado = "cancelada";
    $entrega->fecha_de_procesamiento_real = $hoy;
    $entrega->save();
    return;
  }

  public function entregada_adelantada($entrega, $hoy){
    $entrega->estado = "entregada";
    $entrega->adelanta = 1;
    $entrega->entregas_adelantadas = 1;
    $entrega->fecha_de_procesamiento_real = $hoy;
    $entrega->save();
    return;
  }

  public function entregada($entrega, $hoy){
    $entrega->estado = "entregada";
    $entrega->fecha_de_procesamiento_real = $hoy;
    $entrega->save();
    return;
  }

  public function check_proper_day(){

    $entregas = Entrega::all();
    $test_data = array();
    foreach ($entregas as $entrega) {
      $fecha_de_entrega_potencial = $entrega->fecha_de_entrega_potencial;
      $date_time = new DateTime($fecha_de_entrega_potencial);
      $dia_entrega = $date_time->format("N");
      $pedido = $entrega->pedido()->get()->first();
      $dia_pedido = $pedido->dia_de_entrega;

      if($dia_pedido != $dia_entrega){
        $obj = new \stdClass();
        $obj->pedido_id = $pedido->id;
        $obj->entrega_id = $entrega->id;
        $test_data[] = $obj;
      }
    }

    dd($test_data);
  }

  public function recover_certain_pedidos_with_entregas(){
    $pedidos = Pedido::all();
    $test_data = array();
    foreach ($pedidos as $pedido) {
      if($pedido->fecha_de_restauracion != null){
        $obj = new \stdClass();
        $obj->pedido = $pedido->getAttributes();
        $entregas = $pedido->entregas()->get();
        $entregas_procesadas = array();
        foreach ($entregas as $entrega) {
          $entregas_procesadas[] = $entrega->getAttributes();
        }
        $obj->entregas = $entregas_procesadas;
        $test_data[] = $obj;
      }
    }
    dd($test_data);
  }

}
