<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Validator;

use App\Pedido;
use App\User;
use App\Empresa;

use App\Particular;
use App\Empleado;

use App\ProductosSolicitado;

class PedidoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($filter)
    {
      $user = Auth::user();

      if($user->role != "admin" && $user->role != "empleado"){
        return response()->json([
          'status' => 'fail',
          'details' => 'Sin autorización'
      ], 403);
      }else{
        if($filter == "true"){
          $pedidos = Pedido::where("visto" , 0)->get();
        }else{
          $pedidos = Pedido::all();
        }

        foreach ($pedidos as $pedido){
          $usuario = $pedido->user()->get();
          $pedido->usuario = $usuario;
        };

        return response()->json([
            'status' => 'success',
            'data' => $pedidos
        ], 200);
      }
    }

    public function get_where(Request $request){

      $pedidos = Pedido::where([
        $request->all()
        ])->get();
      return response()->json([
          'status' => 'success',
          'data' => $pedidos
      ], 200);
    }

    public function get_where_empleado()
    {
      $user = Auth::user();

      $pedidos = Pedido::where([
        "repartidor_habitual" => $user->name
        ])->get();

      foreach ($pedidos as $pedido){
        $usuario = $pedido->user()->get();
        $pedido->usuario = $usuario;
      };

      return response()->json([
        'status' => 'success',
        'data' => $pedidos
      ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      // Acá me llegan los datos del pedido y ademas un array de productos

      // ACA tengo que checkear el email contra la base de datos de usuarios
      // Si no esta , lo creo, sino , agrego el producto con ese user id



       $user = $request->user; // nombre email y rol

       $user_registrado = User::where("email" ,$user['email'])->get()->first();

       if(count($user_registrado) == 0){

         $user = User::create([
           'name' => $user['name'],
           'email' => $user['email'],
           'password' => Hash::make("SIMONE"),
           'role' => $user["role"]
         ]);

         $role = $user["role"];

         if($role == "empresa"){
           $empresa = new Empresa;
           $empresa->user_id = $user->id;
           $empresa->save();
         }else if($role == "particular"){
           $particular = new Particular;
           $particular->user_id = $user->id;
           $particular->save();
         }

         $id = $user->id;

       }else{
         $id = $user_registrado->id;
       }


       $pedido = (object) $request->pedido;
       $pedido->user_id = $id;

       $pedido = (array) $pedido;

       $pedido = Pedido::create($pedido);
       $id_pedido = $pedido->id;

       $productos = (array) $request->productos;
       foreach($productos as $producto){
         $producto = (object) $producto;
         $id_producto = $producto->id;
         $cantidad = $producto->cantidad;

         $pivot = ProductosSolicitado::create(array(
           "producto_id" => $id_producto,
           "cantidad" => $cantidad,
           "pedido_id" => $id_pedido
         ));
       };

       return response()->json([
         'status' => 'success',
         'data' => $pedido->user_id
       ], 200);

    }

    public function storeAdmin(Request $request)
    {
      // Acá me llegan los datos del pedido y ademas un array de productos

      $messages = [
          'exists'=> 'No existe un usuario con ese ID'
      ];

      $user = Auth::user();

     if ($user->role != "admin") {
       return response()->json([
           'status' => 'fail',
           'data' => "No autorizado"
       ], 200);
     }else{
       $pedido = (array) $request->pedido;

       $validator = Validator::make($pedido, [
          'user_id' => 'required|exists:users,id'
      ], $messages);

      if ($validator->fails()) {
        return response()->json([
            'status' => 'fail',
            'data' => $validator->errors()
        ], 200);
      }

       // El request tiene un parte de pedido y un parte de productos.

       $pedido = Pedido::create($pedido);
       $id_pedido = $pedido->id;

       $productos = (array) $request->productos;
       foreach($productos as $producto){

         $producto = (object) $producto;
         $id_producto = $producto->id;
         $cantidad = $producto->cantidad;

         $pivot = ProductosSolicitado::create(array(
           "producto_id" => $id_producto,
           "cantidad" => $cantidad,
           "pedido_id" => $id_pedido
         ));
       };

       return response()->json([
           'status' => 'success',
           'data' => $pedido
       ], 200);
     }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
      $id = Auth::id();
      $pedidos = Pedido::where('user_id', $id)->get();
      foreach($pedidos as $pedido){
        $entregas = $pedido->entregas()->get();
        $productos = $pedido->productos()->get();

        $pedido->entregas = $entregas;
        $pedido->productos = $productos;
      }

      return response()->json([
          'status' => 'success',
          'data' => $pedidos
      ], 200);
    }

    public function showAdmin($id)
    {
      $user = Auth::user();

      if($user->role != "admin" && $user->role != "empleado"){
        return response()->json([
          'status' => 'fail',
          'mensaje' => 'Sin autorización'
      ], 403);
      }



      $pedido = Pedido::where('id', $id)->get()[0];

      $entregas = $pedido->entregas()->get();
      $usuario = $pedido->user()->get();
      $productos = $pedido->productos()->get();
      foreach($productos as $producto){

        $pedido_id = $producto->pivot->pedido_id;
        $producto_id = $producto->pivot->producto_id;

        $pivotCompleto = ProductosSolicitado::where("pedido_id" , $pedido_id)
        ->where("producto_id", $producto_id)->get();

        $cantidad = $pivotCompleto[0]->cantidad;
        $producto->cantidad = $cantidad;
        $producto->pivot->cantidad = $cantidad;
      }
      $pedido->entregas = $entregas;
      $pedido->productos = $productos;
      $pedido->usuario = $usuario;
      $rol;
      if($usuario[0]->role == "particular"){
        $rol = Particular::where("user_id" , $user->id)->get()->first();
      }else{
        $rol = Empresa::where("user_id" , $user->id)->get()->first();
      }

      $pedido->rol = $rol;

      return response()->json([
        'status' => 'success',
        'data' => $pedido
      ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateAdmin(Request $request)
    {
      $messages = [
          'integer'    => 'Debe ingresar un dato numérico en el campo :attribute',
          'string'    => 'Debe ingresar un dato de texto en el campo :attribute'
      ];
      $user = Auth::user();

      if($user->role != "admin"){
        return response()->json([
          'status' => 'fail',
          'mensaje' => 'Sin autorización'
      ], 403);
      }else{

        // Acá el tipo puede cambiar detalles del pedido o tambien detalles de los productos del pedido.

        $pedido = (object) $request->pedido;
        $productos = (object) $request->productos;

        $id_pedido = $pedido->id;

        Pedido::where("id", $id_pedido)->update((array) $pedido);

        // Elimino todo los pivots con este pedido y los renuevo.

        ProductosSolicitado::where("pedido_id" , $id_pedido)->delete();

        foreach($productos as $producto){

          // No, me llegan todos los productos del pedido.
          // Debería eliminar todos los campos anterior en producto solicitado e ingresar estos nuevos.
          $producto = (object) $producto;
          $id_producto = $producto->id;
          $cantidad = $producto->cantidad;

          $pivot = ProductosSolicitado::create(array(
            "producto_id" => $id_producto,
            "cantidad" => $cantidad,
            "pedido_id" => $id_pedido
          ));
        }


          return response()->json([
          'status' => 'success',
          'data' => "Se guardó el pedido con éxito"
        ], 200);
      }
    }

}
