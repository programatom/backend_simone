<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Empresa;
use Validator;


class EmpresaController extends Controller
{
    public function update($id)
    {

      $empresa = Empresa::findOrFail($id);
      $messages = [
          'integer'    => 'Debe ingresar un dato numérico en el campo :attribute',
          'string'    => 'Debe ingresar un dato de texto en el campo :attribute'
      ];
      /*
      request()->validate([
          "nombre" => "string",
          "razon_social" => "string",
          "CUIT" => "string",
          "dom_fiscal" => "string",
          "saldo" => "string",
          "telefono" => "string",
          "calle" => "string",
          "numero" => "string",
          "piso" => "string",
          "depto" => "string",
          "localidad" => "string",
          "provincia" => "string",
          "nombre_receptor" => "string",
          "observaciones" => "string"
     ], $messages);
     */
     $request = request()->all();
     unset($request["_method"]);
     unset($request["_token"]);
     $empresa->update($request);
     return redirect("usuarios")->with("success" , "Se actualizó la empresa con éxito");
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
