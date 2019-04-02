<?php

namespace App\Http\Controllers;

use App\MuestrasPedido;
use Illuminate\Http\Request;

class MuestrasPedidoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json([
          "status"=> "success",
          "data"=> MuestrasPedido::all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      MuestrasPedido::create($request->all());
      return response()->json([
        "status"=> "success",
        "data"=> "Se creó el pedido de muestra grátis con éxito"
      ]);
    }

    public function buscar_pedidos_de_muestra_sin_ver()
    {
      return response()->json([
        "status"=> "success",
        "data"=> MuestrasPedido::where("visto", 0)->get()
      ]);
    }

    public function update(Request $request)
    {
      $id_pedido = $request->id;
      $pedido_muestra = MuestrasPedido::where("id", $id_pedido)->update([
        "visto" => 1
      ]);
      return response()->json([
        "status"=> "success",
        "data"=> "Se actualizo el campo visto"
      ]);
    }
}
