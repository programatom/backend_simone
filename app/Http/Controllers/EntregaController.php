<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Entrega;
use App\Pedido;
use App\User;
use App\ExceptionProduct;


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

    public function get_entregas_habituales_empleado(Request $request){
       $repartidor_habitual = $request->repartidorHabitual;
       $fecha_actual = $request->fechaActual;
       $fecha =
       $pedidos_habituales = Pedido::where([
         "repartidor_habitual" => $repartidor_habitual,
         "estado" => "en proceso"
         ])->get();

       $array_pedidos_habituales_hoy = array();
       foreach ($pedidos_habituales as $pedido_habitual) {

         $obj_habitual_pedido_hoy = new \StdClass();
         $entregas_habituales_hoy = array();

         $entregas_pedido = $pedido_habitual->entregas();
         foreach ($entregas_pedido as $entrega) {
           if($entrega->fecha_de_entrega == $fecha_actual){
             $entregas_habituales_hoy[] = $entrega;
           }
         }
         $user = $pedido_habitual->user()->get();
         $productos = $pedido_habitual->productos()->get();
         $obj_habitual_pedido_hoy->entregas = $entregas_habituales_hoy;
         $obj_habitual_pedido_hoy->pedido = $pedido_habitual;
         $obj_habitual_pedido_hoy->usuario = $user;
         $obj_habitual_pedido_hoy->productos = $productos;
         $array_pedidos_habituales_hoy[] = $obj_habitual_pedido_hoy;
       }

       return response()->json([
         "status"=> "success",
         "data"=>$array_pedidos_habituales_hoy
       ],200);
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




    public function procesar_entrega(Request $request){

      $entrega_id = $request->entrega_id;
      $data_request = (object) $request->data;

      $estado_entrega = $data_request->estado;
      $is_derivada = $data_request->derivada;

      $pedido_entrega = Entrega::where("id" , $entrega_id)
                                 ->get()
                                 ->first()
                                 ->pedido()->get()->first();

      $pedido_id = $pedido_entrega->id;

      $monto_pedido_con_descuento = $pedido_entrega->monto_con_desc;

      // HACER VALIDACION, NO SE PUEDEN DERIVAR ENTREGAS PROCESADAS !
      // NO SE PUEDEN MODIFICAR ENTREGAS DERIVADAS !
      if($is_derivada == 1){

        $entrega = $this->procesar_entrega_database($entrega_id, $data_request, 0 , 0, $pedido_entrega);

        return response()->json([
          'status' => 'success',
          'data' => $entrega
        ], 200);

      }
      if($estado_entrega == "cancelada"){

        $entrega = $this->procesar_entrega_database($entrega_id, $data_request, 0 , 0, $pedido_entrega);

        return response()->json([
          'status' => 'success',
          'data' => $entrega
        ], 200);

      }

      if($estado_entrega == "entregada"){

        $entrega = $this->procesar_entrega_database($entrega_id, $data_request, $monto_pedido_con_descuento , 0, $pedido_entrega);

        return response()->json([
          'status' => 'success',
          'data' => $entrega
        ], 200);

      }

      $paga_con = $data_request->paga_con;

      if($estado_entrega == "entregada_con_modificaciones"){
        $exception_productos = $data_request->exception_product;
        if( $exception_productos == 1){
          $productos = $data_request->productos;

          // Primero borro todos los datos de la tabla

          $excepciones_hechas = ExceptionProduct::where("entrega_id" , $entrega_id)->get();

          if(count($excepciones_hechas) > 0){
            $excepciones_hechas->delete();
          }

          foreach($productos as $producto){
            $producto = (object) $producto;
            $producto_id = $producto->id;
            $cantidad = $producto->cantidad;

            ExceptionProduct::create([
              "pedido_id" => $pedido_id,
              "entrega_id" => $entrega_id,
              "producto_id" => $producto_id,
              "cantidad" => $cantidad
            ]);
          }

          $entrega = $this->procesar_entrega_database($entrega_id, $data_request, $paga_con , 1, $pedido_entrega);
          return response()->json([
            'status' => 'success',
            'data' => $entrega
          ], 200);

        }else{
          $entrega = $this->procesar_entrega_database($entrega_id, $data_request, $paga_con , 0, $pedido_entrega);
          return response()->json([
            'status' => 'success',
            'data' => $entrega
          ], 200);
        }
      }


    }

    public function procesar_entrega_database($entrega_id, $data_request, $paga_con , $exception_product, $pedido_entrega){

      if( $paga_con != $pedido_entrega->monto_con_desc){

        // CAMBIO SALDO AL CLIENTE
        $diferencia_pago = $paga_con - $pedido_entrega->monto_con_desc;
        $user = $pedido_entrega->user()->get()->first();
        $saldo_usuario = $user->saldo;
        $user->saldo = $saldo_usuario + $diferencia_pago; // SUMA SALDO POSITIVO
        $user->save();

      }

      $entrega = Entrega::where("id", $entrega_id)->update([
        "estado" => $data_request->estado,
        "fecha_de_procesamiento_real" => $data_request->fecha_de_procesamiento_real,
        "exception_product"=> $exception_product,
        "paga_con" => $paga_con,
        "derivada" => $data_request->derivada
      ]);

      return $entrega;
    }

}
