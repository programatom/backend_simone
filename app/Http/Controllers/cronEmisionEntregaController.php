<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Pedido;
use App\Entrega;
use DateTime;
use DateInterval;


class cronEmisionEntregaController extends Controller
{

    // Debería analizar como voy a  manejar los cambios del usuario sobre esta lógica. Si el tipo cancela un pedido un dia y en el mismo dia lo vuelve a levantar, se emiten dos entregas para el mismo dia.

    // 1 - No puede haber dos entregas el mismo dia del mismo pedido.

    public function reestablecer_estado_en_proceso(Request $request){

      // Si un usuario modifica un estado de alarma, entonces se tiene que reestablecer el estado de ese pedido y de esa entrega
      $pedido = Pedido::where("id" , $request->id)->get()->first();
      $this->emission_logic($pedido);
      return response()->json([
        "status" => "success",
        "data" => $this->emission_logic($pedido)
      ]);
    }

    public function iniciar_proceso_cron(){

      $pedidos = Pedido::all();
      $arrayRespuestas = array();
      foreach($pedidos as $pedido){
        $respuesta = new \stdClass ();
        $respuesta->respuestaFuncion = $this->emission_logic($pedido);
        $respuesta->pedido = $pedido;

        $arrayRespuestas[] = $respuesta;
        /* TEST FIRST TABLE VALUE
        return response()->json([
          "data"=>$this->emission_logic($pedido)]);
      */
    }
      return response()->json([
        "data"=> "Se barrio toda la tabla de pedidos",
        "dataCron" => $arrayRespuestas
      ],200);
    }

    public function emission_logic($pedido){

      $hoy =date("Y/m/d");
      //$hoy = "2019/05/01";

      // CHECKEADO EL TEMA DE LAS FECHAS. SE EMITEN AL DIA QUE DEBEN. CHECKEADO EL SISTEMA DE ALARMA. FALTA REVISAR LOGICA DE PEDIDOS DIARIOS.

      if($this->pedido_with_no_data($pedido)){
        return "no data";
      }


      // Ahora deberia revisar la última entrega emitida de este pedido. Si el pedido de esa entrega es X entonces si la fecha potencial de entrega de la entrega + periodicidad == a fecha de hoy entonces checkeo la entrega.



      $ultima_entrega = $pedido->entregas()->get()->last();
      // Nunca se emitió una entrega? Si o si se emite una si el pedido está en proceso
      $usuario_del_pedido = $pedido->user()->get()->first();
      $periodicidad_pedido = $pedido->periodicidad;
      $dias_de_entrega = $pedido->dia_de_entrega;


      // Si el pedido esta discountinuado entonces se debería realizar alguna lógica para su relanzamiento
      if($pedido->estado == "en proceso"){

        // SI NUNCA SE EMITIO UNA ENTREGA (INICIAL PEDIDO) O ESTABA CANCELADO SE EMITE UNA ENTREGA ASAP

        if($ultima_entrega == null || $pedido->danger < 0 ){

          $this->emitir_entrega_partiendo_de_hoy($periodicidad_pedido, $hoy,
        $dias_de_entrega, $pedido, $usuario_del_pedido);

          return "se reestablece un cancelado o inicial";
        }
        $fecha_de_procesamiento_real = $ultima_entrega->fecha_de_procesamiento_real;
        $last_potential_date = $ultima_entrega->fecha_de_entrega_potencial;


        // Se entrego?


        if($fecha_de_procesamiento_real != ""){

          // Se realizó la entrega emitida. Se emite la siguiente que va a ser en el dia igual al dia de entrega que esté despues de la suma entre la fecha de creacion y los dias a añadir por periodicidad en el caso de que no haya dias de alarma. Antes checkeo si se entregó dentro del rango previsto o si estuvo algunos días sin procesarse el pedido


          if($pedido->danger == 0){

            // Acá emito una entrga basandome en la ultima entrega potencial e incrementando su valor por la periodicidad. No hubo danger

            return $this->emitir_entrega_pedido_a_tiempo($periodicidad_pedido, $last_potential_date, $pedido, $usuario_del_pedido);


          }else{

            // Calculo la nueva fecha potencial en base a la fecha de entrega real no a la potencial. Porque se pasó de dias. Aca debería revisar el tema de los casos diarios

            return $this->emitir_entrega_pedido_a_destiempo($periodicidad_pedido, $fecha_de_procesamiento_real, $pedido, $usuario_del_pedido);

          }
        }

        // Si no se entrego? Si la nueva fecha potencial es hoy
        // Antes de hacer nada tengo que checkear que hoy sea mas grande que la fecha potencial de la entrega si es hoy es mas grande entonces no se entrego cuando se debería, pero podemos estar en el mismo periodo

        $dateTimestamp1 = strtotime($hoy);
        $dateTimestamp2 = strtotime($ultima_entrega->fecha_de_entrega_potencial);

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

            // Estamos en el mismo dia o mas de cuando se debería entregar, per en el mismo periodo franco

            return $this->alarm_state($pedido);
          }else{

            // No hay fecha de entrega, y además estamos fuera de periodo. DANGER
            return $this->danger_state($pedido);
          }
        }else{
          return $this->en_proceso_state($pedido);
        }
      }else if($pedido->estado == "cancelado"){
        return $this->cancelado_state($pedido);
      }



      }

      public function emitir_entrega_pedido_a_destiempo($periodicidad_pedido, $fecha_de_procesamiento_real, $pedido, $usuario_del_pedido){
        $nueva_fecha_potencial = $this->get_new_potencial_date($periodicidad_pedido, $fecha_de_procesamiento_real, $pedido->dia_de_entrega);

        return $this->emitir_entrega($pedido, $usuario_del_pedido, $nueva_fecha_potencial);

      }

      public function emitir_entrega($pedido, $usuario_del_pedido, $nueva_fecha_potencial){
        // 1 - No puede haber dos entregas del mismo pedido en el mismo dia
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

      public function emitir_entrega_pedido_a_tiempo($periodicidad_pedido, $last_potential_date, $pedido, $usuario_del_pedido){

        $nueva_fecha_potencial = $this->get_new_potencial_date($periodicidad_pedido, $last_potential_date);

        return $this->emitir_entrega($pedido, $usuario_del_pedido, $nueva_fecha_potencial);


      }

      public function pedido_with_no_data($pedido){

        if($pedido->dia_de_entrega == "" || $pedido->repartidor_habitual == ""){

          // El pedido es nuevo y aun faltan datos, no se emite una entrega porque faltan las variables

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
        return "proceso state achieved";
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
        return "DANGER";
      }

      public function emitir_entrega_partiendo_de_hoy($periodicidad_pedido, $hoy,
    $dias_de_entrega, $pedido, $usuario_del_pedido){
        $nueva_fecha_potencial = $this->get_new_potencial_date($periodicidad_pedido, $hoy, $dias_de_entrega);

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

            return $this->get_next_date_with_this_day($dia_de_entrega, $fecha_piso);
          }
          case "quincenal":
          if($dia_de_entrega == ""){
            return date('Y/m/d', strtotime($fecha_piso. ' + 14 days'));
          }else{
            return $this->get_next_date_with_this_day($dia_de_entrega, $fecha_piso);

          }
          case "mensual":
          if($dia_de_entrega == ""){
            return date('Y/m/d', strtotime($fecha_piso. ' + 28 days'));
            //return $this->get_new_date_plus_one_month($fecha_piso);
          }else{
            return $this->get_next_date_with_this_day($dia_de_entrega, $fecha_piso);
          }
          default:

          // Acá debería emitise en el proxima dia, teniendo en cuenta el dia actual si hoy es ese dia, entonces evaluo
          // sumo dias al dia de hoy hasta que el format de la fecha resultante sea igual al numero de dia que necesito

          $dias_de_entrega = explode(",", $dia_de_entrega );
          $last_potential_date_obj = new \DateTime($fecha_piso);
          $siguiente_N_dia_de_entrega = "0";
          $index_dia_de_entrega_hoy = 0;

          foreach($dias_de_entrega as $key => $dia_de_entrega){
            if($dia_de_entrega == $fecha_piso_obj->format("N")){
              // Tengo que ver en que punto de la semana estoy, si hoy es dia de entrega el siguiente dia de entrega es hoy
              $siguiente_N_dia_de_entrega = $dia_de_entrega;
              $index_dia_de_entrega_hoy = $key;
            }
          }

          if($siguiente_N_dia_de_entrega != "0"){
            return $this->get_next_date_with_this_day($siguiente_N_dia_de_entrega , $fecha_piso);
          }

          // Si no, entonces estoy en un dia de la semana donde no hay entrega
          // la proxima entrega potencial seria el proximo elemento del array, o ver si estoy en el ultimo index del array

          $index_siguiente_dia = $index_dia_de_entrega_hoy + 1;
          if($index_siguiente_dia > count($dias_de_entrega) - 1){
            $siguiente_N_dia_de_entrega = $dias_de_entrega[0];
          }else{
            $siguiente_N_dia_de_entrega = $dias_de_entrega[$index_siguiente_dia];
          }
          return $this->get_next_date_with_this_day($siguiente_N_dia_de_entrega , $fecha_piso);
        }

      }
      // $day , $last_potential_date = ""
      public function get_next_date_with_this_day($day , $fecha_piso){
        /*

        $day = $request->dia;
        $fecha_piso = $request->last_potential_date;

        */

        $format_N_date_check = 0;
        $protector_de_loop_infinito = 0;
        $new_emission_date = "";
        $date_counter = 0;

        while( $day != $format_N_date_check){

          $protector_de_loop_infinito = $protector_de_loop_infinito + 1;
          if($protector_de_loop_infinito > 10){
            return "loopinfinito";
            break;
          }


          $new_emission_date = date('Y/m/d', strtotime($fecha_piso.' + '.$date_counter.' day'));
          $date_counter = $date_counter + 1;
          $date_time_1 = new \DateTime($new_emission_date);
          $format_N_date_check = $date_time_1->format("N");
      }
      return $new_emission_date;
      }

    }
