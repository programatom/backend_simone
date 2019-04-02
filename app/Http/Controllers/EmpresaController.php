<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Empresa;
use Validator;


class EmpresaController extends Controller
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

        $empresas = Empresa::all();

        foreach ($empresas as $empresa) {
            $usuario = $empresa->user()->get();
            $empresa->usuario = $usuario;
            $pedidos = $empresa->pedido()->get();
            foreach($pedidos as $pedido){
              $entregas = $pedido->entrega()->get();
              $pedido->entregas = $entregas;
            }
            $empresa->pedidos = $pedidos;
        }

        return response()->json([
            'status' => 'success',
            'data' => $empresas
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
      $empresa = Empresa::where('user_id', $id)->get();

      return response()->json([
          'status' => 'success',
          'data' => $empresa
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
      $id = Auth::id();

      $user = Auth::user();
      if($user->role != "admin"){
        $id = Auth::id();
        $request->request->add(['user_id' => $id]);
      }else{
        $id = $request->user_id;
      }

      $validator = Validator::make($request->all(), [
          "nombre" => "string",
          "razon_social" => "string",
          "CUIT" => "string",
          "dom_fiscal" => "string",
          "saldo" => "integer",
          "telefono" => "integer",
          "calle" => "string",
          "numero" => "integer",
          "piso" => "integer",
          "depto" => "string",
          "localidad" => "string",
          "provincia" => "string",
          "nombre_receptor" => "string",
          "observaciones" => "string"
     ], $messages);

       if ($validator->fails()) {
         return response()->json([
             'status' => 'fail',
             'data' => $validator->errors()
         ], 200);
       }else{
         $empresa = Empresa::where('user_id', $id);
         $empresa->update($request->all());
         return response()->json([
           'status' => 'success',
           'data' => $empresa->get()
         ], 200);
       }

    }

    public function update_web(Request $request){
      $id = $request->id;
      $data = (array) $request->data;
      $empresa = Empresa::where('user_id', $id);
      $empresa->update($data);
      return response()->json([
        'status' => 'success',
        'data' => $empresa->get()
      ], 200);
    }
}
