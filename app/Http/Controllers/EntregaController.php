<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Entrega;
use App\Pedido;
use App\User;
use App\ProductoEntrega;
use App\SaldoCliente;

use App\Particular;
use App\Empresa;
use App\ProductosSolicitado;


use Illuminate\Support\Facades\Auth;
use Validator;

class EntregaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $user = Auth::user();

      if($user->role != "admin"){
        return response()->json([
          'status' => 'fail',
          'details' => 'Sin autorización'
      ], 403);
      }else{

        $entregas = Entrega::all();

        foreach ($entregas as $entrega) {

            $usuario = $entrega->user()->get();
            $entrega->usuario = $usuario[0];
        }

        return response()->json([
            'status' => 'success',
            'data' => $entregas
        ], 200);
      }
    }

    public function get_entregas_danger(){
      $user = Auth::user();

      $repartidor_habitual = $user->name;

      $pedidos_habituales = Pedido::where([
        "repartidor_habitual" => $repartidor_habitual,
        "estado" => "en proceso",
        "danger" => 1,
        ])->get();

      $pedidos_excepcionales = Pedido::where([
        "repartidor_excepcional" => $repartidor_habitual,
        "estado" => "en proceso",
        "danger" => 1
        ])->get();
      $habituales_excepcionales = array_merge($pedidos_habituales->all(),$pedidos_excepcionales->all());
      $array_entregas = $this->entrega_object($habituales_excepcionales, "todas");

      return response()->json([
        "status"=>"success",
        "data"=> $array_entregas,
        "nombre_empleado" =>$user->name
      ]);
    }

    public function get_from_date_to(Request $request){

      $fecha_inicial = $request->desde;
      $fecha_final = $request->hasta;


      $entregas = Entrega::whereBetween('fecha_de_entrega_potencial', [$fecha_inicial, $fecha_final])->get();

      $ids_pedidos = [];

      foreach ($entregas as $entrega) {
        array_push($ids_pedidos, $entrega->pedido_id);
      }

      $user = Auth::user();

      $repartidor_habitual = $user->name;

      $pedidos_habituales = Pedido::where([
        "repartidor_habitual" => $repartidor_habitual,
        "estado" => "en proceso",
        ]);

      $pedidos_excepcionales = Pedido::where([
        "repartidor_excepcional" => $repartidor_habitual,
        "estado" => "en proceso",
        ]);

      $pedidos_habituales = $pedidos_habituales->whereIn(
        "id", $ids_pedidos)->get();

      $pedidos_excepcionales = $pedidos_excepcionales->whereIn(
          "id", $ids_pedidos)->get();

      $habituales_excepcionales = array_merge($pedidos_habituales->all(), $pedidos_excepcionales->all());

      $array_entregas_date_filter = $this->entrega_object($habituales_excepcionales, "todas");

      return response()->json([
        "status"=>"success",
        "data"=> $array_entregas_date_filter
      ]);
    }

    public function get_entregas_con_alarma_y_excepcionales(){
      $user = Auth::user();

      $repartidor_habitual = $user->name;

      $pedidos_habituales = Pedido::where([
        "repartidor_habitual" => $repartidor_habitual,
        "estado" => "en proceso",
        "alarma" => 1,
        ])->get();

      $pedidos_excepcionales = Pedido::where([
        "repartidor_excepcional" => $repartidor_habitual,
        "estado" => "en proceso",
        ])->get();
      $habituales_excepcionales = array_merge($pedidos_habituales->all(),$pedidos_excepcionales->all());
      $array_entregas = $this->entrega_object($habituales_excepcionales, "todas");

      return response()->json([
        "status"=>"success",
        "data"=> $array_entregas,
        "nombre_empleado" =>$user->name
      ]);

    }

    public function get_entregas_habituales_empleado_hoy(){

       $user = Auth::user();

       $repartidor_habitual = $user->name;

       $pedidos_habituales = Pedido::where([
         "repartidor_habitual" => $repartidor_habitual,
         "estado" => "en proceso"
         ])->get();

       $array_entregas_habituales_hoy = $this->entrega_object($pedidos_habituales, "hoy");

       return response()->json([
         "status"=> "success",
         "data"=>$array_entregas_habituales_hoy
       ],200);
    }


    public function entrega_object($pedidos_habituales, $filtro_entregas_dia){
      $array_pedidos_habituales_hoy = array();
      $fecha_hoy = date("Y/m/d");

      foreach ($pedidos_habituales as $pedido_habitual) {

        $obj_habitual_pedido_hoy = new \StdClass();

        $user = $pedido_habitual->user()->get()->first();
        $rol;

        if($user->role == "particular"){
          $rol = Particular::where("user_id" , $user->id)->get()->first();
        }else{
          $rol = Empresa::where("user_id" , $user->id)->get()->first();
        }
        $obj_habitual_pedido_hoy->rol = $rol;
        $obj_habitual_pedido_hoy->pedido = $pedido_habitual;
        $obj_habitual_pedido_hoy->usuario = $user;

        $entrega_is_procesada = false;
        $productos_entregados = [];
        if($filtro_entregas_dia == "hoy"){
          $entregas_habituales_hoy = array();
          $entregas_pedido = $pedido_habitual->entregas()->get();
          foreach ($entregas_pedido as $entrega) {
            if($entrega->fecha_de_entrega_potencial == $fecha_hoy){
              $entregas_habituales_hoy[] = $entrega;
            }
          }
          if(count($entregas_habituales_hoy) > 0){
            $obj_habitual_pedido_hoy->entrega = $entregas_habituales_hoy[0];
          }else{
            $obj_habitual_pedido_hoy->entrega = null;
          }

        }else{
          $entregas_pedido = $pedido_habitual->entregas()->get();
          if(count($entregas_pedido) > 0){
            $obj_habitual_pedido_hoy->entrega = $entregas_pedido[0];
            if($entregas_pedido[0]->estado != "sin procesar"){
              $entrega_is_procesada = true;
              $productos_entregados = $entregas_pedido[0]->productos_entregados()->get();
            }
          }else{
            $obj_habitual_pedido_hoy->entrega = null;
          }
        }
        $productos = [];

        if($entrega_is_procesada){
          $productos = $productos_entregados;
        }else{
          $productos = $pedido_habitual->productos()->get();
          foreach($productos as $producto){

            $pedido_id = $producto->pivot->pedido_id;
            $producto_id = $producto->pivot->producto_id;

            $pivotCompleto = ProductosSolicitado::where("pedido_id" , $pedido_id)
            ->where("producto_id", $producto_id)->get();

            $cantidad = $pivotCompleto[0]->cantidad;
            $producto->cantidad = $cantidad;
          }
        }

        $obj_habitual_pedido_hoy->productos = $productos;
        $array_pedidos_habituales_hoy[] = $obj_habitual_pedido_hoy;

      }
      return $array_pedidos_habituales_hoy;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $messages = [
          'integer'    => 'Debe ingresar un dato numérico en el campo :attribute',
          'requires'    => 'El campo :attribute es requerido'
      ];
      // SE PUEDE GENERAR UNA ENTREGA NUEVA E INGRESARLA EN EL SISTEMA
      // BUSCAR MANERA DE QUE SE CAMBIEN LOS PRODUCTOS ENTREGADOS

      // user_id DEBE MANDAR EL USER ID
      // pedido_id EL PEDIDO ID
      // fecha_de_entrega_potencial ES "-1""
      // fecha_de_procesamiento_real ES IGUAL A LA FECHA ENVIADA
      // derivada ES IGUAL A CERO
      // estado ENTREGADA
      // observaciones
      // proxima_entrega_potencial
      // tipo_de_pedido

      $validator = Validator::make($request->all(),[
        "fecha_de_procesamiento_real" => "required",
        "user_id" => "required",
        "pedido_id" => "required",
      ],$messages);

      if ($validator->fails()) {
        return response()->json([
            'status' => 'fail',
            'data' => $validator->errors()
        ], 200);
      }
      $datos_default_entrega_creada = [
        "estado" => "entregado",
        "fecha_de_entrega_potencial" => "-1",
      ];

      $array_request = $request->all();
      $entrega = Entrega::create(array_merge($datos_default_entrega_creada, $array_request));
      return response()->json([
          'status' => 'success',
          'data' => $entrega
      ], 200);
    }

    public function showAdmin($id)
    {


      $user = Auth::user();
      if($user->role != "admin"){
        return response()->json([
          'status' => 'fail',
          'mensaje' => 'Sin autorización'
      ], 403);
      }

      $entrega = Entrega::where('id', $id)->get();

      $pedido = $entrega->pedido()->get()->first();
      $productos = $pedido->productos()->get();

      $entrega->pedido = $pedido;
      $entrega->productos = $productos;

      return response()->json([
        'status' => 'success',
        'data' => $entrega
      ], 200);

    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id_pedido)
    {
      $id = Auth::id();
      $entregasPedido = Entrega::where('user_id', $id)
                                ->where('pedido_', $id_pedido)->get();

      return response()->json([
          'status' => 'success',
          'data' => $entregasPedido
      ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {

     $id_entrega = $request->$id_entrega;

     $entrega = Entrega::where("id", $id_entrega)->get();

     $entrega->update((array) $request->update);

     return response()->json([
       'status' => 'success',
       'data' => $entrega
     ], 200);

    }

    public function reintentar_entrega($entrega_id){
      $entrega = Entrega::where("id", $entrega_id)->update([
        "reintentar" => 1
      ]);
      return response()->json([
        'status' => 'success',
        'data' => $entrega
      ], 200);
    }

    public function derivar_entrega($entrega_id,$data_request,$pedido_entrega){
      $entrega = $this->procesar_entrega_database_save($entrega_id, $data_request, 0, $pedido_entrega);

      return response()->json([
        'status' => 'success',
        'data' => $entrega
      ], 200);
    }

    public function crear_entrega($request){

      // pedido id
      // producto
      $paga_con = $request->paga_con;
      $entrega = Entrega::create([
        "user_id" => $request->user_id,
        "pedido_id" => $request->pedido_id,
        "fecha_de_entrega_potencial" => date("Y/m/d"),
        "fecha_de_procesamiento_real" => date("Y/m/d"),
        "estado" => "entregada",
        "observaciones" => $request->observaciones,
        "paga_con" => $paga_con,
        "out_of_schedule" => 1
      ]);

      $entrega_id = $entrega->id;
      $productos_entregados = $request->productos_entregados;
      $monto_a_pagar = $request->monto_a_pagar;
      $user = User::where("id", $user_id)->get()->first();

      $this->guardar_entrega_de_producto($entrega_id, $productos_entregados);
      $this->actualizar_saldo_cliente($entrega_id, "entregada", $monto_a_pagar, $paga_con, $user);

      return response()->json([
        "status" => "success",
        "data" => $entrega
      ]);

    }




    public function procesar_entrega(Request $request){

      $entrega_id = $request->entrega_id;

      if($entrega_id == -1){
        return $this->crear_entrega($request);
      }

      $data_request = (object) $request->data;

      $is_derivada = $data_request->derivada;
      $reintentar = $data_request->reintentar;


      $pedido_entrega = Entrega::where("id" , $entrega_id)
                                 ->get()
                                 ->first()
                                 ->pedido()->get()->first();

      $pedido_id = $pedido_entrega->id;

      if($reintentar == 1){
        return $this->reintentar_entrega($entrega_id);
      }

      // HACER VALIDACION, NO SE PUEDEN DERIVAR ENTREGAS PROCESADAS !
      // NO SE PUEDEN MODIFICAR ENTREGAS DERIVADAS !
      if($is_derivada == 1){
        return $this->derivar_entrega($entrega_id,$data_request,$pedido_entrega);
      }

      $estado_entrega = $data_request->estado;


      if($estado_entrega == "cancelada"){
        $productos_entregados = $data_request->productos_entregados;
        $this->guardar_entrega_de_producto($entrega_id, $productos_entregados);

        $entrega = $this->procesar_entrega_database_save($entrega_id, $data_request, 0 , $pedido_entrega);

        return response()->json([
          'status' => 'success',
          'data' => $entrega
        ], 200);
      }

      $paga_con = $data_request->paga_con;
      $user = Entrega::where("id" , $entrega_id)->get()->first()->user()->get()->first();

      if($estado_entrega == "entregada"){
        $productos_entregados = $data_request->productos_entregados;
        $this->guardar_entrega_de_producto($entrega_id, $productos_entregados);

        $entrega = $this->procesar_entrega_database_save($entrega_id, $data_request, $paga_con , $pedido_entrega);

        return response()->json([
          'status' => 'success',
          'data' => $entrega
        ], 200);
      }

    }

    public function guardar_entrega_de_producto($entrega_id, $productos_entregados){
      // 1 - Una entrega puede tener solo una entrada en la tabla de productos entregados. Los productos se guardan en distintas filas de la tabla, pero si se encuentra un campo con el id de la entrega, se borra todo y se agregan los nuevos campos.

      // NOTA: Lo demás son datos de actualizacion sobreescribibles.
      $productos_entregados = $productos_entregados;
      $query_productos = ProductoEntrega::where("entrega_id" , $entrega_id);
      $productos_registrados = $query_productos->get();

      if(count($productos_registrados) > 0){
        $query_productos->delete();
      }

      foreach($productos_entregados as $producto){
        $producto = (object) $producto;
        $producto_id = $producto->id;
        $cantidad = $producto->cantidad;
        $precio = $producto->precio;
        $nombre = $producto->nombre;
        ProductoEntrega::create([
          "cantidad" => $cantidad,
          "entrega_id" => $entrega_id,
          "producto_id" => $producto_id,
          "precio" => $precio,
          "nombre" => $nombre
        ]);
      }

      return;

    }

    public function procesar_entrega_database_save($entrega_id, $data_request, $paga_con , $pedido_entrega){

      // CHECKEAR PROCESAMIENTO DE SALDO

      $estado = $data_request->estado;
      $monto_a_pagar = $data_request->monto_a_pagar;
      $user = $pedido_entrega->user()->get()->first();
      $this->actualizar_saldo_cliente($entrega_id, $estado, $monto_a_pagar, $paga_con, $user);

      $entrega = Entrega::where("id", $entrega_id)->update([
        "estado" => $data_request->estado,
        "fecha_de_procesamiento_real" => date("Y/m/d"),
        "paga_con" => $paga_con,
        "derivada" => $data_request->derivada,
        "reintentar" => $data_request->reintentar,
        "adelanta" => $data_request->adelanta,
        "entregas_adelantadas" => $data_request->entregas_adelantadas,
        "observaciones" => $data_request->observaciones
      ]);

      return $entrega;
    }

    public function actualizar_saldo_cliente($entrega_id, $estado,$monto_a_pagar, $paga_con, $user){
      // 1 - SIEMPRE QUE SE PROCESA UNA ENTREGA SE BORRAN TODOS LOS CAMPOS DE SALDO. SOLAMEMNTE CUANDO ESTÁ ENTREGADA PUEDE HABER CAMBIOS EN EL SALDO DE UN CLIENTE.

      $estado_entrega = $estado;
      SaldoCliente::where("entrega_id" , $entrega_id)->delete();

      if($estado_entrega != "entregada"){
        return;
      }

      $monto_a_pagar = $monto_a_pagar;
      if($paga_con != $monto_a_pagar){
        $saldo_cliente = $paga_con - $monto_a_pagar;
        SaldoCliente::create([
          "monto_asignado" => $saldo_cliente,
          "entrega_id" => $entrega_id,
          "user_id" => $user->id
        ]);

        $todos_los_saldos_usuario = SaldoCliente::where("user_id" , $user->id)->get();
        $saldo_final = 0;
        foreach($todos_los_saldos_usuario as $saldo){
          $monto_asignado = $saldo->monto_asignado;
          $saldo_final = $saldo_final + $monto_asignado;
        }
        $user->saldo = $saldo_final;
        $user->save();
        return;
      }else{
        return;
      }
    }

}
