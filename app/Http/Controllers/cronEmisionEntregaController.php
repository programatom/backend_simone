<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Pedido;
use App\Entrega;
use DateTime;
use DateInterval;


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
        "user_id" => $usuario_del_pedido->id,
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
        $tz = 'America/Argentina/Buenos_Aires';
        $timestamp = time();
        $dt = new \DateTime("now", new \DateTimeZone($tz));
        $hoy = $dt->format('Y/m/d');
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

      // Nunca se emitió una entrega? Si o si se emite una si el pedido está en proceso

      $usuario_del_pedido = $pedido->user()->get()->first();
      $periodicidad_pedido = $pedido->periodicidad;
      $dia_de_entrega = $pedido->dia_de_entrega;


      // Si el pedido esta discountinuado entonces se debería realizar alguna lógica para su relanzamiento

      if($pedido->estado == "en proceso"){

        // Si nunca se emitió una entrega y el pedido está en proceso entonces se emite una entrega

        if($ultima_entrega == null ||
           $pedido->danger < 0){

          $this->emitir_entrega_partiendo_de_hoy($periodicidad_pedido, $hoy,
        $dia_de_entrega, $pedido, $usuario_del_pedido);

          return "se reestablece un cancelado, emite un inicial o un pedido adelantado";

        }

        $fecha_de_procesamiento_real = $ultima_entrega->fecha_de_procesamiento_real;
        $last_potential_date = $ultima_entrega->fecha_de_entrega_potencial;

        // Se entrego?

        if($ultima_entrega->estado == "entregada" || $ultima_entrega->estado == "cancelada" ){

          // Se realizó la entrega emitida. Se emite la siguiente que va a ser en el dia igual al dia de entrega que esté despues de la suma entre la fecha de creacion y los dias a añadir por periodicidad en el caso de que no haya dias de alarma. Antes checkeo si se entregó dentro del rango previsto o si estuvo algunos días sin procesarse el pedido

          // En este paso debería checkear el filtro counter y ver si se adelanto o no entregas. Si adelanto, el usuario va a especificar cuantas entregas quiere que se adelanten. Como el estado es entregado, la logica viene a este punto todos las iteraciones a intentar de emitir la próxima entrega. Opciones:

          // 1 - Multiplicar el Nº de entregas a filtrar por la periodicidad y descontar de ese valor hasta que de cero. Pero si se entrega en un dia distinto al potencial
          // POSIBLE ECUACIÓN: Nº EntregasAFiltrar * Periodicidad - TiempoPasadoDesdeLaUltimaFechaPotencial
          // 2 - Fijar una fecha a partir de la cual se debe seguir emitiendo entregas. El usuario especifica cuantas entregas adelanta el pedido.
          // En un pedido semanal por ejemplo, si se adelantan 3 entregas, la ecuacion sería, próxima fecha = $last_potential_date + peridiocidad_int * entregas adelantadas + 1. Osea el dia siguiente de la entrega potencial se vuelve a empezar a emitir entregas. Ese dia se emite una entrega partiendo del dia de hoy.
          // Si el pedido estaba en danger, entonces debería checkear si estoy en el mismo dia de entrega del pedido, si lo estoy entonces emito desde ese dia, sino busco la fecha anterior con el mismo dia y busco una próxima fecha desde ese dia.

          if($ultima_entrega->adelanta == 1){

            $obj_periodicidad = new \stdClass();
            $obj_periodicidad->semanal = 7;
            $obj_periodicidad->quincenal = 14;
            $obj_periodicidad->mensual = 28;

            if($pedido->fecha_de_restauracion == null){
              $entregas_a_adelantar = $ultima_entrega->entregas_adelantadas;
              $periodicidad_number = $obj_periodicidad->$periodicidad_pedido;
              $dias_adelantados = $periodicidad_number * $entregas_a_adelantar;
              if($pedido->danger == 1){
                $previous_date_with_correct_day = $this->get_next_or_previous_date_with_this_day($pedido->dia_de_entrega, $hoy , "previous");
                $fecha_de_restauracion = date('Y/m/d', strtotime($previous_date_with_correct_day. ' + '.$dias_adelantados.' days'));
                $pedido->fecha_de_restauracion = $fecha_de_restauracion;
                $pedido->save();
                return "adelantada";
              }else{
                $fecha_de_restauracion = date('Y/m/d', strtotime($last_potential_date. ' + '.$dias_adelantados.' days'));
                $pedido->fecha_de_restauracion = $fecha_de_restauracion;
                $pedido->save();
                return "adelantada";
              }
            }else if($pedido->fecha_de_restauracion == $hoy){
              $pedido->fecha_de_restauracion = "";
              $pedido->adelanta = 0;
              $pedido->save();
              return $this->emitir_entrega_partiendo_de_hoy($periodicidad_pedido, $hoy, $dia_de_entrega, $pedido, $usuario_del_pedido);
            }else{
              return $this->en_proceso_state($pedido);
            }
          }

          if($pedido->danger == 0){

            // Acá emito una entrga basandome en la ultima entrega potencial e incrementando su valor por la periodicidad. No hubo danger
            if($pedido->date_change_alert == 1){
              $pedido->date_change_alert = 0;
              $pedido->save();
              return $this->emitir_entrega_partiendo_de_hoy($periodicidad_pedido, $last_potential_date, $dia_de_entrega, $pedido, $usuario_del_pedido);
            }else{
              return $this->emitir_entrega_pedido_a_tiempo($periodicidad_pedido, $last_potential_date, $pedido, $usuario_del_pedido);
            }


          }else{

            // Calculo la nueva fecha potencial en base a la fecha de entrega real no a la potencial. Porque se pasó de dias.

            return $this->emitir_entrega_pedido_a_destiempo($periodicidad_pedido, $fecha_de_procesamiento_real, $pedido, $usuario_del_pedido);

          }
        }

        // NO SE ENTREGÓ
        // Antes de hacer nada tengo que checkear que hoy sea mas grande que la fecha potencial de la entrega si es hoy es mas grande entonces no se entrego cuando se debería, pero podemos estar en el mismo periodo

        $dateTimestamp1 = strtotime($hoy);
        $dateTimestamp2 = strtotime($last_potential_date);

        if( $dateTimestamp1 >  $dateTimestamp2){

          // Si para hoy no se procesó entonces se levanta el estado de alarma
          // Ahora checkeo si estoy dentro del periodo en el que se podría entregar ésta entrega. Envío como fecha piso la entrega potencial. No mando dia de entrega. En el caso de mensual, semanal y quincenal, debería enviar sin dia de entrega. Suma y listo, sé si estoy dentro del periodo. En cambio en los diarios, podría levantar danger state de una si no se entrego el pedido de hoy.

          $nueva_fecha_potencial = "";
          switch($periodicidad_pedido){
            case "diario":
            return $this->danger_state($pedido);
            default:
            $nueva_fecha_potencial = $this->get_new_potencial_date($periodicidad_pedido, $ultima_entrega->fecha_de_entrega_potencial);
          }

          $dateTimestamp1 = strtotime($hoy);
          $dateTimestamp2 = strtotime($nueva_fecha_potencial);

          if($nueva_fecha_potencial > $hoy){

            // Estamos en el mismo dia o mas de cuando se debería entregar, pero en el mismo periodo franco

            return $this->alarm_state($pedido);
          }else{

            // No hay fecha de entrega, y además estamos fuera de periodo. DANGER
            return $this->danger_state($pedido);
          }
        }else{
          return $this->en_proceso_state($pedido);
        }
      }else if($pedido->estado == "discontinuado"){
        return $this->cancelado_state($pedido);
      }



      }

      public function emitir_entrega_pedido_a_destiempo($periodicidad_pedido, $fecha_de_procesamiento_real, $pedido, $usuario_del_pedido){
        $nueva_fecha_potencial = $this->get_new_potencial_date($periodicidad_pedido, $fecha_de_procesamiento_real, $pedido->dia_de_entrega);

        return $this->emitir_entrega($pedido, $usuario_del_pedido, $nueva_fecha_potencial);

      }



      public function emitir_entrega_pedido_a_tiempo($periodicidad_pedido, $last_potential_date, $pedido, $usuario_del_pedido){

        $nueva_fecha_potencial = $this->get_new_potencial_date($periodicidad_pedido, $last_potential_date);

        return $this->emitir_entrega($pedido, $usuario_del_pedido, $nueva_fecha_potencial);


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
        $pedido->dias_sin_procesar_danger = 0;
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
        $pedido->dias_sin_procesar_danger = $pedido->dias_sin_procesar_danger + 1;
        $pedido->alarma = 0;
        $pedido->danger = 1;
        $pedido->save();
        return "DANGER state";
      }

      public function emitir_entrega_partiendo_de_hoy($periodicidad_pedido, $hoy,
    $dia_de_entrega, $pedido, $usuario_del_pedido){
        $nueva_fecha_potencial = $this->get_new_potencial_date($periodicidad_pedido, $hoy, $dia_de_entrega);

        return $this->emitir_entrega($pedido, $usuario_del_pedido, $nueva_fecha_potencial);

      }

      public function get_new_potencial_date($periodicidad_pedido, $fecha_piso, $dia_de_entrega = ""){
        // $last_potential_date formateada siempre en 'Y/m/d'
        // Si no paso un dia de entrega, queire decir que se tiene que emitir sumando el periodo

        switch($periodicidad_pedido){
          case "semanal":
          if($dia_de_entrega == ""){
            // Fecha piso es la ultima entrega potencial

            return date('Y/m/d', strtotime($fecha_piso. ' + 7 days'));
          }else{

            // El proximo dia que coincida con el dia de entrega despues de la fecha piso sale la emision

            // Si paso dia de entrega entonces es una entrega que o es la primera vez que se emite, viene de discontinuación o viene de ser cancelado a estar en proceso nuevamente. Siempre se envia la fecha piso, que en el caso que esté pasada de dias es la fecha de entrega real. Si es la primera vez es el dia de hoy. Si viene de estar disontinuado es el dia de hoy.

            return $this->get_next_or_previous_date_with_this_day($dia_de_entrega, $fecha_piso);
          }
          case "quincenal":
          if($dia_de_entrega == ""){
            return date('Y/m/d', strtotime($fecha_piso. ' + 14 days'));
          }else{
            return $this->get_next_or_previous_date_with_this_day($dia_de_entrega, $fecha_piso);
          }
          case "mensual":
          if($dia_de_entrega == ""){
            return date('Y/m/d', strtotime($fecha_piso. ' + 28 days'));
            //return $this->get_new_date_plus_one_month($fecha_piso);
          }else{
            return $this->get_next_or_previous_date_with_this_day($dia_de_entrega, $fecha_piso);
          }
          /*
          default:

          // Acá debería emitise en el proxima dia, teniendo en cuenta el dia actual si hoy es ese dia, entonces evaluo
          // sumo dias al dia de hoy hasta que el format de la fecha resultante sea igual al numero de dia que necesito
          $dia_de_entrega = explode(",", $dia_de_entrega );
          $last_potential_date_obj = new \DateTime($fecha_piso);
          $siguiente_N_dia_de_entrega = "0";
          $index_dia_de_entrega_hoy = 0;

          foreach($dia_de_entrega as $key => $dia_de_entrega){
            if($dia_de_entrega == $fecha_piso_obj->format("N")){
              // Tengo que ver en que punto de la semana estoy, si hoy es dia de entrega el siguiente dia de entrega es hoy
              $siguiente_N_dia_de_entrega = $dia_de_entrega;
              $index_dia_de_entrega_hoy = $key;
            }
          }

          if($siguiente_N_dia_de_entrega != "0"){
            return $this->get_next_or_previous_date_with_this_day($siguiente_N_dia_de_entrega , $fecha_piso);
          }

          // Si no, entonces estoy en un dia de la semana donde no hay entrega
          // la proxima entrega potencial seria el proximo elemento del array, o ver si estoy en el ultimo index del array

          $index_siguiente_dia = $index_dia_de_entrega_hoy + 1;
          if($index_siguiente_dia > count($dia_de_entrega) - 1){
            $siguiente_N_dia_de_entrega = $dia_de_entrega[0];
          }else{
            $siguiente_N_dia_de_entrega = $dia_de_entrega[$index_siguiente_dia];
          }
          return $this->get_next_or_previous_date_with_this_day($siguiente_N_dia_de_entrega , $fecha_piso);
          */
        }

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
        $protector_de_loop_infinito = 0;
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
