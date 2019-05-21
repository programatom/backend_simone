<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Pedido;
use App\Entrega;
use App\Http\Controllers\EntregaController;
use DateTime;
use DateInterval;

// El script es un emisor de entregas pausable. Lo que hace es evaluar para cada entrega de cada pedido su posición cronologica cuando se ejecuta el script. Si la entrega está dentro del periodo en el que se debe entregar, o se pasó y hubo una entrega nueva que no se emite porque si chocaría con la entrega anterior que nunca se procesó entonces se está en estado de danger, porque hay una entrega que no se sabe que pasó. Una vez que sé que hay una entrega emitida para el pedido, tengo que evaluar antes que nada su fecha potencial y la fecha de hoy. Si entre ellas hay mas de un periodo, entonces podría estar en estado de danger, si no entonces quiere decir que solo es un estado de alarma y si la fecha es igual a hoy entonces todavia esa entrega no debería modificarse.

// Luego se procede a checkear en que fecha se procesó por primera vez o no.
// No hay ultima entrega ->
  // Se emite partiendo de hoy.

// Si la ultima entrega emitida ->

    // está procesada ("cancelada" o "entregada") ->  dentro del rango de alarma y estoy dentro de ese mismo rango y no es una entrega que adelante, entonces emito la proxima entrega en un "periodo + hoy". Si estoy fuera de ese rango y no es una entrega que adelante otras entregas, entonces la emito desde hoy hacia el próximo dia de entrega. En ambos casos, el pedido esta en el estado de "en proceso".
        // la ultima entrega adelanta entregas ->
              // tiene fecha de restauración?
                  // SI
                  // ya se calculó una fecha de restauración. Estoy en el
                    // pasado -> no se emite ninguna entrega.
                    // futuro o hoy -> emito una entrega para ese pedido a partir de hoy.
                  // NO
                  // Estoy dentro del rango de alarma o de danger de la entrega?
                    // alarma -> proxima fecha es ultima fecha ideal + periodo * Nº entregas adelantadas + 1
                    // danger -> calculo la misma fecha de recuperacion que antes, ultima fecha ideal + periodo * Nº entregas adelantadas + 1, si
                        //HOY ES MAS GRANDE
                          // Emito una entrega en el prox dia de entrega partiendo de hoy
                        // HOY ES MAS CHICO o IGUAL A HOY
                          // no se emite ninguna entrega.



// Está "sin procesar" -> y estoy dentro de ese mismo rango, el pedido está en el estado de alarma. O estoy afuera del rango el pedido esta en estado de danger. No se emite ninguna entrega.

//



class cronEmisionEntregaController extends Controller
{



    public function emitir_entrega($pedido, $usuario_del_pedido, $nueva_fecha_potencial){

      // 1 - No puede haber dos entregas el mismo dia del mismo pedido.

      $ultima_entrega = $pedido->entregas()->get()->last();
      if($ultima_entrega != null){
        // Si hay una entrega, se checkea la nueva entrega potencial con la anterior
        $fecha_de_entrega_potencial_anterior = $ultima_entrega->fecha_de_entrega_potencial;
        $fecha_de_entrega_potencial_anterior_obj = new \DateTime($fecha_de_entrega_potencial_anterior);

        $fecha = $fecha_de_entrega_potencial_anterior_obj->format("Y/m/d");

        if($fecha_de_entrega_potencial_anterior == $nueva_fecha_potencial){
          return $this->en_proceso_state($pedido);
        }
      }

      $entrega_nueva = Entrega::create([
        "pedido_id" => $pedido->id,
        "fecha_de_entrega_potencial" => $nueva_fecha_potencial
      ]);
      return $this->en_proceso_state($pedido);
    }

    public function reestablecer_estado_en_proceso(Request $request){

      // Si un usuario modifica un estado de alarma, entonces se tiene que reestablecer el estado de ese pedido y de esa entrega
      $pedido = Pedido::where("id" , $request->id)->get()->first();
      $this->emission_logic($pedido);
      return response()->json([
        "status" => "success",
        "data" => $this->emission_logic($pedido)
      ]);
    }

    public function iniciar_proceso_cron($hoy = null){

      if ($hoy == null){
        $entrega_controller = new EntregaController();
        $hoy = $entrega_controller->hoy();
      }

      $pedidos = Pedido::all();
      $arrayRespuestas = array();
      foreach($pedidos as $pedido){
        $respuesta = new \stdClass ();
        $respuesta->respuestaFuncion = $this->emission_logic($pedido, $hoy);
        $respuesta->pedido = $pedido;

        $arrayRespuestas[] = $respuesta;
        /* TEST FIRST TABLE VALUE
        return response()->json([
          "data"=>$this->emission_logic($pedido)]);
      */
    }
      return $arrayRespuestas;
    }

    public function emission_logic($pedido, $hoy){

      if($this->pedido_with_no_data($pedido)){
        return "no data";
      }

      $ultima_entrega = $pedido->entregas()->where("out_of_schedule", 0)->get()->last();
      $usuario_del_pedido = $pedido->user()->get()->first();
      $periodicidad_pedido = $pedido->periodicidad;
      $dia_de_entrega = $pedido->dia_de_entrega;


      // Si el pedido esta discountinuado no se evalua su estado o el sistema está en pausa no se ejecuta al emisor.

      if($pedido->estado == "en proceso"){

        // Si nunca se emitió una entrega y el pedido está en proceso entonces se emite una entrega

        if($ultima_entrega == null){

          $this->emitir_entrega_partiendo_de_hoy($periodicidad_pedido, $hoy,
        $dia_de_entrega, $pedido, $usuario_del_pedido);

          return "Primer entrega";

        }

        // Hay una ultima entrega a analizar. En que estado está?

        $fecha_de_procesamiento_real = $ultima_entrega->fecha_de_procesamiento_real;
        $last_potential_date = $ultima_entrega->fecha_de_entrega_potencial;

        // Se procesó?

        if($ultima_entrega->estado == "entregada" || $ultima_entrega->estado == "cancelada" ){

          // Logica si la entrega adelanta otras entregas y bloquea la emision

          if($ultima_entrega->adelanta == 1 && $ultima_entrega->estado != "cancelada"){

            // la ultima entrega adelanta entregas ->
                  // tiene fecha de restauración?
                      // SI
                      // ya se calculó una fecha de restauración. Estoy en el
                        // pasado -> no se emite ninguna entrega.
                        // futuro o hoy -> emito una entrega para ese pedido a partir de hoy.
                      // NO
                      // Estoy dentro del rango de alarma o de danger de la entrega?
                        // alarma -> proxima fecha es ultima fecha ideal + periodo * Nº entregas adelantadas + 1
                        // danger -> calculo la misma fecha de recuperacion que antes, ultima fecha ideal + periodo * Nº entregas adelantadas + 1, si
                            //HOY ES MAS GRANDE O IGUAL
                              // Emito una entrega en el prox dia de entrega partiendo de hoy
                            // HOY ES MAS CHICO
                              // no se emite ninguna entrega.

            $obj_periodicidad = new \stdClass();
            $obj_periodicidad->semanal = 7;
            $obj_periodicidad->quincenal = 14;
            $obj_periodicidad->mensual = 28;

            if($pedido->fecha_de_restauracion == null){

              $entregas_a_adelantar = $ultima_entrega->entregas_adelantadas;
              $periodicidad_number = $obj_periodicidad->$periodicidad_pedido;
              // Se agrega el + 1 en ésta ecuación así se restaura el pedido justo un dia después de que se debería emitir.
              $dias_adelantados = $periodicidad_number * $entregas_a_adelantar + 1;
              $fecha_de_restauracion = date('Y/m/d', strtotime($last_potential_date.' + '.$dias_adelantados.' days'));

              if($this->rango_actual($hoy, $last_potential_date, $periodicidad_pedido) == "rango_alarma"){


                $pedido->fecha_de_restauracion = $fecha_de_restauracion;
                $pedido->save();
                return $this->en_proceso_state($pedido);

              }else{

                $hoy_timestamp = strtotime($hoy);
                $fecha_de_restauracion_timestamp = strtotime($fecha_de_restauracion);

                if($hoy_timestamp >= $fecha_de_restauracion_timestamp){
                  return $this->emitir_entrega_partiendo_de_hoy($periodicidad_pedido, $hoy, $dia_de_entrega, $pedido, $usuario_del_pedido);
                }else{
                  $pedido->fecha_de_restauracion = $fecha_de_restauracion;
                  $pedido->save();
                  return $this->en_proceso_state($pedido);
                }
              }

            }else{
              $restauracion_timestamp = strtotime($pedido->fecha_de_restauracion);
              $hoy_timestamp = strtotime($hoy);

              if( $restauracion_timestamp > $hoy_timestamp){
                return $this->en_proceso_state($pedido);
              }else{
                $pedido->fecha_de_restauracion = "";
                $pedido->save();
                return $this->emitir_entrega_partiendo_de_hoy($periodicidad_pedido, $hoy, $dia_de_entrega, $pedido, $usuario_del_pedido);
              }
            }
          }

          // Logica si la entrega no adelanta
          /*
          dentro del rango de alarma y estoy dentro de ese mismo rango y no es una entrega que adelante, entonces emito la proxima entrega en un "periodo + hoy". Si estoy fuera de ese rango y no es una entrega que adelante otras entregas, entonces la emito desde hoy hacia el próximo dia de entrega. En ambos casos, el pedido esta en el estado de "en proceso".
          */



          if($this->rango_actual($hoy, $last_potential_date, $periodicidad_pedido) == "rango_alarma"){

            // Acá emito una entrega basandome en la ultima entrega potencial e incrementando su valor por la periodicidad. No hubo danger

            return $this->emitir_entrega_pedido_a_tiempo($periodicidad_pedido, $last_potential_date, $pedido, $usuario_del_pedido);


          }else{

            // Como estoy dentro del rango danger, emito una entrega para el próximo dia que sea dia de entrega a partir de hoy.

            return $this->emitir_entrega_partiendo_de_hoy($periodicidad_pedido, $hoy, $dia_de_entrega, $pedido, $usuario_del_pedido);
          }
        }

        // NO SE ENTREGÓ

        // Antes de hacer nada tengo que checkear que hoy sea mas grande que la fecha potencial de la entrega si es hoy es mas grande entonces no se entrego cuando se debería, pero podemos estar en el mismo periodo

        $hoy_timestamp = strtotime($hoy);
        $last_potential_date_timestamp = strtotime($last_potential_date);

        if( $hoy_timestamp > $last_potential_date_timestamp){

          // Si hoy es mas grande que la fecha potencial checkeo el rango actual

          if($this->rango_actual($hoy, $last_potential_date, $periodicidad_pedido) == "rango_danger"){
            return $this->danger_state($pedido);
          } else{
            return $this->alarm_state($pedido);
          }
        }else{
          return $this->en_proceso_state($pedido);
        }
      }else if($pedido->estado == "discontinuado"){
        return $this->cancelado_state($pedido);
      }
      }

      public function rango_actual($hoy, $ultima_fecha_potencial, $periodicidad){

        $obj_periodicidad = new \stdClass();
        $obj_periodicidad->semanal = 7;
        $obj_periodicidad->quincenal = 14;
        $obj_periodicidad->mensual = 28;

        $add_days = $obj_periodicidad->$periodicidad;

        $limite_alarma = date('Y/m/d', strtotime($ultima_fecha_potencial.' + '.$add_days.' day'));

        $hoy_timestamp = strtotime($hoy);
        $limite_alarma_timestamp = strtotime($limite_alarma);

        if( $hoy_timestamp > $limite_alarma_timestamp ){
          return "rango_danger";
        } else if ( $hoy_timestamp <  $limite_alarma_timestamp){
          return "rango_alarma";
        } else if ( $hoy_timestamp == $limite_alarma_timestamp){

          // Acá no importa el estado, en ambos casos la próxima fecha de entrega es hoy

          return "rango_alarma";
        }
      }

      public function pedido_with_no_data($pedido){
        if($pedido->dia_de_entrega == "" || $pedido->repartidor_habitual_id == 0){

          $pedido->faltan_datos = 1;
          $pedido->save();

          return true;
        }else{
          $pedido->faltan_datos = 0;
          $pedido->save();
          return false;
        }
      }

      public function en_proceso_state($pedido){
        $pedido->alarma = 0;
        $pedido->danger = 0;
        $pedido->save();
        return "proceso state";
      }

      public function cancelado_state($pedido){
        $pedido->alarma = -1;
        $pedido->danger = -1;
        $pedido->save();
        return "cancelado state";
      }

      public function alarm_state($pedido){
        $pedido->alarma = 1;
        $pedido->danger = 0;
        $pedido->save();
        return "alarm state";
      }

      public function danger_state($pedido){
        $pedido->alarma = 0;
        $pedido->danger = 1;
        $pedido->save();
        return "DANGER state";
      }

      public function emitir_entrega_pedido_a_tiempo($periodicidad_pedido, $last_potential_date, $pedido, $usuario_del_pedido){

        $new_date_patch = "";

        if($pedido->date_change_alert == 1){
          $pedido->date_change_alert = 0;
          $pedido->save();
          $new_date_patch = $pedido->dia_de_entrega;
        }

        $nueva_fecha_potencial = $this->get_new_potencial_date($periodicidad_pedido, $last_potential_date, "", $new_date_patch);

        return $this->emitir_entrega($pedido, $usuario_del_pedido, $nueva_fecha_potencial);
      }

      public function emitir_entrega_partiendo_de_hoy($periodicidad_pedido, $hoy,
    $dia_de_entrega, $pedido, $usuario_del_pedido){
        $nueva_fecha_potencial = $this->get_new_potencial_date($periodicidad_pedido, $hoy, $dia_de_entrega);

        return $this->emitir_entrega($pedido, $usuario_del_pedido, $nueva_fecha_potencial);
      }


      public function get_new_potencial_date($periodicidad_pedido, $fecha_piso, $dia_de_entrega = "", $date_change_calibrate = ""){
        // $last_potential_date formateada siempre en 'Y/m/d'
        // Si no paso un dia de entrega, queire decir que se tiene que emitir sumando el periodo
        $obj_periodicidad = new \stdClass();
        $obj_periodicidad->semanal = 7;
        $obj_periodicidad->quincenal = 14;
        $obj_periodicidad->mensual = 28;

        if($dia_de_entrega == ""){
          // Fecha piso es la ultima entrega potencial
          if($date_change_calibrate != ""){
            $fecha_ancla = date('Y/m/d', strtotime($fecha_piso. ' + '.$obj_periodicidad->$periodicidad.' days'));
            return $this->get_closest_date_with_this_day($date_change_calibrate , $fecha_ancla);
          }else{
            return date('Y/m/d', strtotime($fecha_piso. ' + '.$obj_periodicidad->$periodicidad_pedido.' days'));
          }
        }else{

          // El proximo dia que coincida con el dia de entrega despues de la fecha piso sale la emision

          // Si paso dia de entrega entonces es una entrega que o es la primera vez que se emite, viene de discontinuación o viene de ser cancelado a estar en proceso nuevamente. Siempre se envia la fecha piso, que en el caso que esté pasada de dias es la fecha de entrega real. Si es la primera vez es el dia de hoy. Si viene de estar disontinuado es el dia de hoy.

          return $this->get_next_or_previous_date_with_this_day($dia_de_entrega, $fecha_piso);
        }
      }

      public function get_closest_date_with_this_day($day , $fecha_ancla){

        $new_emission_date = "";

        $format_N_date_check = 0;
        $date_counter_future = 0;
        $future_date = "";

        while( $day != $format_N_date_check_future){
          $future_date = date('Y/m/d', strtotime($fecha_ancla.' + '.$date_counter_future.' day'));
          $date_counter_future = $date_counter_future + 1;
          $date_time = new \DateTime($new_emission_date);
          $format_N_date_check = $date_time->format("N");
        }

        $format_N_date_check = 0;
        $date_counter_past = 0;
        $past_date = "";

        while( $day != $format_N_date_check){
          $past_date = date('Y/m/d', strtotime($fecha_ancla.' + '.$date_counter_past.' day'));
          $date_counter_past = $date_counter_past + 1;
          $date_time = new \DateTime($new_emission_date);
          $format_N_date_check = $date_time->format("N");
        }
        // Si esta mas lejos la fecha futura que la pasada, se elije las mas cercana, la pasada.

        if($date_counter_future > $date_counter_past){
          $new_emission_date = $past_date;
        }else{
          $new_emission_date = $future_date;
        }

        return $new_emission_date;
      }

      // $day , $last_potential_date = ""
      public function get_next_or_previous_date_with_this_day($day , $fecha_piso , $next_or_previous = "next")
      {
        /*

        */
        if($next_or_previous == "next"){
          $modifier = "+";
        }else{
          $modifier = "-";
        }

        $format_N_date_check = 0;
        $new_emission_date = "";
        $date_counter = 0;

        while( $day != $format_N_date_check){
          $new_emission_date = date('Y/m/d', strtotime($fecha_piso.' '.$modifier.' '.$date_counter.' day'));
          $date_counter = $date_counter + 1;
          $date_time_1 = new \DateTime($new_emission_date);
          $format_N_date_check = $date_time_1->format("N");
      }
        return $new_emission_date;
      }

    }
