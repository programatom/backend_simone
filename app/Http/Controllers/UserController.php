<?php

namespace App\Http\Controllers;

use App\User;
use App\Pedido;
use App\Empleado;
use App\Particular;
use App\Empresa;
use Validator;
use App\ProductoEntrega;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;



use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{

    public function index()
    {
      $user = Auth::user();
      return view("usuarios.index", [
        "usuarios" => User::orderBy('created_at', 'desc')->paginate(50)
      ]);
    }

    public function create()
    {
      return view("usuarios.create");
    }

    public function store(Request $request)
    {

      $messages = [
          'required'=> 'El campo :attribute es requerido',
          'string'=> 'Debe ingresar un dato de texto en el campo :attribute',
          'email'=> 'Debe ingresar un email válido',
          'confirmed'=> 'Ambas contraseñas deben coincidir!',
          'unique' => 'El email debe ser único',
          'min'=> 'La contraseña debe tener al menos 6 caracteres',
      ];

      $this->validate($request,
      [
          'name' => ['required', 'string', 'max:255'],
          'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
          'password' => ['required', 'string', 'min:6'],
          'role' => ['required','string']
      ],$messages);

      $request = $request->all();

      $user = User::create([
        'name' => $request['name'],
        'email' => $request['email'],
        'password' => Hash::make($request['password']),
        'role' => $request["role"]
      ]);

      $role = $request["role"];

      if($role == "empresa"){
        $empresa = Empresa::create();
        $empresa->user_id = $user->id;
        $empresa->save();
      }else if($role == "particular"){
        $particular = Particular::create();
        $particular->user_id = $user->id;
        $particular->save();
      }


      return redirect("usuarios")->with("success" , "Se creo un nuevo usuario con éxito, en la pantalla de edición podrá agregar los datos de rol");

    }

    public function edit($id){

      $user_data = $this->get_user_data($id);


      return view("usuarios.edit",[
        "usuario" => $user_data->usuario,
        "role_obj" => $user_data->role_obj,
        "particular" => $user_data->role_data,
        "empresa" => $user_data->role_data,
        "productos" => $user_data->productos_entregados
      ]);
    }

    public function get_user_data($id){
      $user_data = new \stdClass();
      $usuario = User::findOrFail($id);

      $user_data->usuario = $usuario;

      $role = $usuario->role;
      $roles_to_show = array("particular" , "empresa");
      $has_role = false;
      $role_data = "";
      if(in_array($role, $roles_to_show)){
        $has_role = true;
        $role_data = DB::table($role.'s')->where("user_id", $id)->get()->first();
      }
      $user_data->role_data = $role_data;

      $pedidos_del_usuario = $usuario->pedidos()->get();
      $productos_entregados = [];

      foreach($pedidos_del_usuario as $pedido){
        $entregas = $pedido->entregas()->get();
        foreach($entregas as $entrega){
          $productos_solicitados = $entrega->productos_entregados()->get();
          foreach($productos_solicitados as $producto_solicitado){
            $productos_entregados[] = $producto_solicitado;
          }
        }
       }

      $role_obj = new \stdClass();
      $role_obj->has_role = $has_role;
      $role_obj->role = $role;
      $user_data->productos_entregados = $productos_entregados;
      $user_data->role_obj = $role_obj;
      return $user_data;
    }

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
    public function update($id)
    {
        // EVALUAR COMO SE MANEJAN  LAS MODIFICACIONES EN EL SALDO
        $user = User::findOrFail($id);

        $messages = [
          'string'=> 'Debe ingresar un dato de texto en el campo :attribute',
          'email'=> 'Debe ingresar un email válido',
          'confirmed'=> 'Ambas contraseñas deben coincidir!',
          'min'=> 'La contraseña debe tener al menos 6 caracteres',
          'integer' => "Debe ingresar un campo numérico en :attribute"

        ];

        $email_enviado = request()->all()["email"];
        $validation_array_email = array();
        if($user->email == $email_enviado){
          $validation_array_email = [ 'string', 'email', 'max:255'];
        }else{
          $validation_array_email = [ 'string', 'email', 'max:255', 'unique:users'];
        }

        request()->validate([
          'name' => [ 'string', 'max:255'],
          'email' => $validation_array_email,
          'password' => [ 'string', 'min:6']
        ] , $messages);

          $request = request()->all();
          unset($request["_method"]);
          unset($request["_token"]);
          $user->update([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => Hash::make($request['password'])
          ]
        );
          return redirect("usuarios")->with("success" , "Se actualizó el usuario con éxito");

    }


}
