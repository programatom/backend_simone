<?php

namespace App\Http\Controllers;

use App\Cupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
class CuponController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      return view("cupones.index",[
        "cupones" => Cupon::all()
      ]);
    }

    public function create()
    {
      return view("cupones.create");
    }

    public function edit($id)
    {
      $cupon = Cupon::findOrFail($id);
      return view("cupones.edit", [
        "cupon" => $cupon,
      ]);
    }

    public function update($id){
      $cupon = Cupon::findOrFail($id);
      $codigo_enviado = request()->all()["codigo"];
      $validation_array = array();
      if($cupon->codigo == $codigo_enviado){
        $validation_array = ["required"];
      }else{
        $validation_array = ["required", "unique:cupons"];
      }
      request()->validate([
        "codigo" => $validation_array,
        "fecha_expiracion" => ['required'],
        "porcentaje_descuento" => ['required'],
        "duracion_descuento" => ['required'],

      ],[
        "required" => "El campo :attribute es requerido",
        "unique" => "El campo :attribute debe ser único"
      ]);
      $request = request()->all();
      unset($request["_method"]);
      unset($request["_token"]);
      $cupon->update($request);
      return redirect("cupones")->with("success" , "Se actualizó el cupón con éxito");
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
            'required'=> 'El campo :attribute es requerido',
            "unique"=> "El campo :attribute debe ser único, ya hay un cupon de descuento con este código!"
        ];

        // NOTA VALIDAR FORMATO DE FECHA Y RANGO DEL DESCUENTO Y AGREGAR GENERADOR AUTOMATICO DE CODIGO

        $this->validate($request, [
          "codigo" => ["required", "unique:cupons"],
          "fecha_expiracion" => "required",
          "porcentaje_descuento" => "required|integer",
          "duracion_descuento" => "required|integer"

       ], $messages);

       $request = $request->all();
       unset($request["_token"]);

       Cupon::create($request);

       return redirect("cupones")->with("success" , "Se creo un nuevo cupón con éxito");
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Cupon  $cupon
     * @return \Illuminate\Http\Response
     */
    public function verify_coupon(Request $request)
    {
      $cupon_codigo = $request->id_cupon;
      $cupon = Cupon::where("codigo" , $cupon_codigo)->get()->first();

      if(count($cupon) == 0){
        return response()->json([
          "status" => "fail",
          "data" => "no_existe"
        ]);
      }
      // LA FECHA SE GUARDA EN LA DB COMO DIA/MES/AÑO

      $fecha_de_expiracion = $cupon->fecha_expiracion;
      $hoy = strtotime(date("d/m/y"));
      $fecha_de_expiracion_obj  = strtotime(date($fecha_de_expiracion));
      $is_expirado = $fecha_de_expiracion_obj < $hoy;

      if($is_expirado){
        return response()->json([
          "status" => "fail",
          "data" => "expiro",
          "fecha_expiracion" => $fecha_de_expiracion
        ]);
      }

      return response()->json([
        "status" => "success",
        "data" => $cupon
      ]);

    }

}
