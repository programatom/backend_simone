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
      $user = Auth::user();

      if($user->role != "admin"){
        return response()->json([
          'status' => 'fail',
          'details' => 'Sin autorización'
      ], 403);
      }else{

        $cupones = Cupon::all();
        foreach($cupones as $cupon){
          $cupon->usos = $cupon->usos()->get();
        };
        return response()->json([
            'status' => 'success',
            'data' => $cupones
        ], 200);
      }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
      $user = Auth::user();

      if($user->role != "admin"){
        return response()->json([
          'status' => 'fail',
          'details' => 'Sin autorización'
      ], 403);
      }else{
        Cupon::where("id",$request->id)->delete();
        return response()->json([
          'status' => 'success'
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
      $user = Auth::user();

      if($user->role != "admin"){
        return response()->json([
          'status' => 'fail',
          'details' => 'Sin autorización'
      ], 403);
      }else{

        $messages = [
            'required'=> 'El campo :attribute es requerido',
            "unique"=> "El campo :attribute debe ser único, ya hay un cupon de descuento con este código!"
        ];
        $validator = Validator::make($request->all(), [
          "codigo" => ["required", "unique:cupons"],
          "fecha_expiracion" => "required",
          "porcentaje_descuento" => "required",
          "tipo" => "required"
       ], $messages);

       if ($validator->fails()) {
         return response()->json([
             'status' => 'fail',
             'data' => $validator->errors()
         ], 200);
       }else{
         $cupon = Cupon::create($request->all());
         return response()->json([
             'status' => 'success',
             'data' => $cupon
         ], 200);
       }
      }
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
