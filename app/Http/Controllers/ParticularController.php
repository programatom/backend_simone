<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Particular;


class ParticularController extends Controller
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

        $particulares = Particular::all();

        foreach ($particulares as $particular) {
            $usuario = $particular->user()->get();
            $particular->usuario = $usuario;
            $pedidos = $particular->pedido()->get();
            foreach($pedidos as $pedido){
              $entregas = $pedido->entrega()->get();
              $pedido->entregas = $entregas;
            }
            $particular->pedidos = $pedidos;
        }

        return response()->json([
            'status' => 'success',
            'data' => $particulares
        ], 200);
      }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

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
      $particular = Particular::where('user_id', $id)->get();

      return response()->json([
          'status' => 'success',
          'data' => $particular
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
      $messages = [
          'integer'    => 'Debe ingresar un dato numérico en el campo :attribute',
          'string'    => 'Debe ingresar un dato de texto en el campo :attribute'
      ];
      $user = Auth::user();
      if($user->role != "admin"){
        $id = Auth::id();
        $request->request->add(['user_id' => $id]);
      }else{
        $id = $request->user_id;
      }

      $validator = Validator::make($request->all(), [
        "user_id" => "integer",
        'nombre' => "string",
        "saldo" => "integer",
        "telefono" => "integer",
        "calle" => "string",
        "numero" => "integer",
        "piso" => "integer",
        "depto" => "string",
        "localidad" => "string",
        "provincia" => "string",
        "observaciones" => "string",
     ], $messages);

       if ($validator->fails()) {
         return response()->json([
             'status' => 'fail',
             'data' => $validator->errors()
         ], 200);
       }else{
         $particular = Particular::where('user_id', $id);
         $particular->update($request->all());
         return response()->json([
           'status' => 'success',
           'data' => $particular->get()
         ], 200);
       }

    }

    public function update_web(Request $request){
      $id = $request->id;
      $data = (array) $request->data;
      $particular = Particular::where('user_id', $id);
      $particular->update($data);
      return response()->json([
        'status' => 'success',
        'data' => $particular->get()
      ], 200);
    }
}
