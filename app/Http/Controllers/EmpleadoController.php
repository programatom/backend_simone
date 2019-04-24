<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;

use App\Empleado;
use App\User;

use Illuminate\Http\Request;

class EmpleadoController extends Controller
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

        //$empleados = Empleado::all();

        $empleados = User::where([
          "role" => "empleado"
        ])->get();

        return response()->json([
            'status' => 'success',
            'data' => $empleados
        ], 200);
      }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Empleado  $empleado
     * @return \Illuminate\Http\Response
     */
    public function show(Empleado $empleado)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Empleado  $empleado
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Empleado $empleado)
    {
      $user = Auth::user();

      if($user->role != "admin"){
        return response()->json([
          'status' => 'fail',
          'details' => 'Sin autorización'
      ], 403);
      }else{

        $id_empleado = $request->id;
        $empleado = Empleado::where("id" , $id_empleado)->update($request->all());

        return response()->json([
          'status' => 'success',
          'data' => $empleado
        ], 200);
      }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Empleado  $empleado
     * @return \Illuminate\Http\Response
     */
    public function destroy(Empleado $empleado)
    {
        //
    }
}
