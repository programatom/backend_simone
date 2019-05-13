<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;

use App\Empresa;
use App\Particular;
use App\Empleado;


class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');

    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
      $messages = [
          'required'=> 'El campo :attribute es requerido',
          'string'=> 'Debe ingresar un dato de texto en el campo :attribute',
          'email'=> 'Debe ingresar un email válido',
          'confirmed'=> 'Ambas contraseñas deben coincidir!',
          'unique' => 'El email debe ser único',
          'min'=> 'La contraseña debe tener al menos 6 caracteres',
      ];
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'role' => ['required','string']
        ],$messages);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $user = User::create([
          'name' => $data['name'],
          'email' => $data['email'],
          'password' => Hash::make($data['password']),
          'role' => $data["role"]
        ]);

        $role = $data["role"];

        if($role == "empresa"){
          $empresa = Empresa::create();
          $empresa->user_id = $user->id;
          $empresa->save();
        }else if($role == "particular"){
          $particular = Particular::create();
          $particular->user_id = $user->id;
          $particular->save();
        }

      return $user;
    }

    protected function registered(Request $request, $user)
    {
        $user->generateToken();

        if(isset($request->web)){
          return redirect("usuarios")->with("success", "Se creó el usuario con éxito");
        }

        return response()->json(['data' => $user->toArray(),
                                 "status" => "success"], 201);
    }
}
