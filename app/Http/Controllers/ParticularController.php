<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Particular;


class ParticularController extends Controller
{

    public function update($id)
    {
      $messages = [
          'integer'    => 'Debe ingresar un dato numérico en el campo :attribute',
          'string'    => 'Debe ingresar un dato de texto en el campo :attribute'
      ];
      $particular = Particular::findOrFail($id);
      /*
      request()->validate([
        'nombre' => "string",
        "telefono" => "string",
        "calle" => "string",
        "numero" => "string",
        "piso" => "string",
        "depto" => "string",
        "localidad" => "string",
        "provincia" => "string",
        "observaciones" => "string",
     ], $messages);
     */
     $request = request()->all();
     unset($request["_method"]);
     unset($request["_token"]);
     $particular->update($request);
     return redirect("usuarios")->with("success" , "Se actualizó el particular con éxito");

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
