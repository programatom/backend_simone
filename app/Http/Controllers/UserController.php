<?php

namespace App\Http\Controllers;

use App\User;
use App\Pedido;
use App\Empleado;
use App\Particular;
use App\Empresa;
use Validator;
use Illuminate\Support\Facades\Hash;



use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
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

        $usuarios = User::all();

        return response()->json([
            'status' => 'success',
            'data' => $usuarios
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\CuponUso  $cuponUso
     * @return \Illuminate\Http\Response
     */
    public function showAdmin($id)
    {
      $user = Auth::user();

      if($user->role != "admin"){
        return response()->json([
          'status' => 'fail',
          'mensaje' => 'Sin autorización'
      ], 403);
      }

      $user = Auth::user()->where('id', $id)->get()[0];
      $pedidos = Pedido::where("user_id" , $id)->get();

      foreach($pedidos as $pedido){
        $productos = $pedido->productos()->get();
        $entregas = $pedido->entregas()->get();
        $pedido->productos = $productos;
        $pedido->entregas = $entregas;
      }

      $user->pedidos = $pedidos;

      if($user->role == "empleado"){
        $empleado = Empleado::where("user_id" , $id)->get();
        $user->data_rol = $empleado;
      }else if ($user->role == "particular"){
        $particular = Particular::where("user_id" , $id)->get();
        $user->data_rol = $particular;
      }else if ($user->role == "empresa"){
        $empresa = Empresa::where("user_id" , $id)->get();
        $user->data_rol = $empresa;
      };



      return response()->json([
        'status' => 'success',
        'data' => $user
      ], 200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\CuponUso  $cuponUso
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        // MISMA FUNCION PARA USUARIO Y ADMIN
        // USUARIO USO ID DEL AUTH, EN ADMIN USO CAMPO id de request

        $messages = [
          'string'=> 'Debe ingresar un dato de texto en el campo :attribute',
          'email'=> 'Debe ingresar un email válido',
          'confirmed'=> 'Ambas contraseñas deben coincidir!',
          'min'=> 'La contraseña debe tener al menos 6 caracteres',
          'integer' => "Debe ingresar un campo numérico en :attribute"

        ];

        $user = Auth::user();

        if($user->role == "admin"){
          $id = $request->id;

          // VALIDACION DE CAMBIO DE EMAIL, SI ES IGUAL AL GUARDADO, SACARLO DEL UPDATE

          $user = User::where("id", $id)->get()[0];
        };

        $email_sent = $request->email;
        if(isset($email_sent)){
          $email_user = $user->email;
          if($email_user == $email_sent){
            $validator_array = $request->only(['password', 'name', "password_confirmation"]);
          }else{
            $validator_array = $request->all();
          }
        }

        $validator = Validator::make($validator_array, [
          'name' => [ 'string', 'max:255'],
          'email' => [ 'string', 'email', 'max:255', 'unique:users'],
          'password' => [ 'string', 'min:6', 'confirmed'],
          "saldo" => ["integer"]
        ],$messages);


        if ($validator->fails()) {
          return response()->json([
              'status' => 'fail',
              'data' => $validator->errors()
          ], 200);
        };

        $user->update([
          'name' => $request['name'],
          'email' => $request['email'],
          'password' => Hash::make($request['password']),
          "saldo" => $request["saldo"]
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $user
        ], 200);

    }


}
