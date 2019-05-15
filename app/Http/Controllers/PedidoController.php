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
use App\Producto;
use App\Rules\DateFormat;


use App\ProductosSolicitado;

class PedidoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      return view("pedidos.index",[
        "pedidos" => Pedido::all()
      ]);
    }


    public function create()
    {
      $users_part_o_empre = User::where(['role' => 'particular'])->orWhere(['role' => 'empresa'])->get();
      $productos = Producto::all();
      $empleados = User::where(['role' => 'empleado'])->get();
      $periodicidad_select = array("semanal","quincenal","mensual");
      $dias_de_entrega = array(
        "1" => "Lunes",
        "2" => "Martes",
        "3" => "Miercoles",
        "4" => "Jueves",
        "5" => "Viernes",
        "6" => "Sabado",
        "7" => "Domingo",
      );
      $formas_de_pago = array(
        "efectivo","cheque","otro"
      );
      return view("pedidos.create", [
        "users" => $users_part_o_empre,
        "productos" => $productos,
        "empleados" => $empleados,
        "periodicidad_select" => $periodicidad_select,
        "dias_de_entrega" => $dias_de_entrega,
        "formas_de_pago" => $formas_de_pago
      ]);
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
       $messages = [
           'required' => 'El campo :attribute es requerido',
           "integer"=> 'El campo precio debe ser un numero',
           "unique"=> 'Ya hay un producto con esa posicion'
       ];

       $validate = $this->validate($request, [
         "user_id" => "required",
         "descuento" => "required",
         "periodicidad" => "required",
         "repartidor_habitual_id" => "required",
         "repartidor_excepcional_id" => "required",
         "estado" => "required",
         "dia_de_entrega" => "required",
         "forma_de_pago" => "required",
         "expiracion_descuento" => ["required", new DateFormat],
       ], $messages);

       $request = $request->all();
       unset($request["_token"]);

       Pedido::create($request);

       return redirect("pedidos")->with("success" , "Se creo un nuevo pedido con éxito, en la pantalla de edición podrá agregar los productos");
    }

    public function producto_pedido(Request $request){
      $count = ProductosSolicitado::where(array(
        "producto_id" => $request->producto_id,
        "pedido_id" => $request->pedido_id,
      ))->count();
      if($count > 0){
        $producto = ProductosSolicitado::where(array(
          "producto_id" => $request->producto_id,
          "pedido_id" => $request->pedido_id,
        ))->get()[0];
        $producto->cantidad = $producto->cantidad + 1;
        $producto->save();
      }else{
        ProductosSolicitado::create(array(
          "producto_id" => $request->producto_id,
          "cantidad" => 1,
          "pedido_id" => $request->pedido_id
        ));
      }
      return redirect(url()->previous());
    }

    public function edit($id){
      $pedido = Pedido::findOrFail($id);

      $entregas = $pedido->entregas()->get();
      $usuario = $pedido->user()->get();
      $productos = $pedido->productos()->get();
      foreach($productos as $producto){
        $producto->cantidad =
        ProductosSolicitado::where(array(
          "producto_id" => $producto->pivot->producto_id,
          "pedido_id" => $producto->pivot->pedido_id,
        ))->get()[0]->cantidad;

      }
      $pedido->entregas = $entregas;
      $pedido->productos = $productos;
      $pedido->usuario = $usuario;
      $empleados = User::where(['role' => 'empleado'])->get();
      $periodicidad_select = array("semanal","quincenal","mensual");
      $dias_de_entrega = array(
        "1" => "Lunes",
        "2" => "Martes",
        "3" => "Miercoles",
        "4" => "Jueves",
        "5" => "Viernes",
        "6" => "Sabado",
        "7" => "Domingo",
      );
      $formas_de_pago = array(
        "efectivo","cheque","otro"
      );
      return view("pedidos.edit", [
        "pedido" => $pedido,
        "productos" => Producto::all(),
        "empleados" => $empleados,
        "periodicidad_select" => $periodicidad_select,
        "dias_de_entrega" => $dias_de_entrega,
        "formas_de_pago" => $formas_de_pago
      ]);
    }

    public function producto_pedido_delete(Request $request){
      $producto_query = ProductosSolicitado::where(array(
        "producto_id" => $request->producto_id,
        "pedido_id" => $request->pedido_id,
      ));
      $count = $producto_query->count();
      if($count > 0){
        $producto = $producto_query->get()->first();
        $cantidad = $producto->cantidad;
        if($cantidad == 1){
          $producto_query->delete();
        }else{
          $producto->cantidad = $producto->cantidad - 1;
          $producto->save();
        }
      }
      return redirect(url()->previous());
    }
    public function update($id){
      $messages = [
          'required' => 'El campo :attribute es requerido',
      ];
      $pedido = Pedido::find($id);
      request()->validate([
        "descuento" => "required",
        "expiracion_descuento" => ["required" , new DateFormat],
      ],$messages);

      $request = request()->all();
      unset($request["_method"]);
      unset($request["_token"]);
      $pedido->update($request);
      return redirect("pedidos")->with("success" , "Se actualizó el pedido con éxito");

    }

}
