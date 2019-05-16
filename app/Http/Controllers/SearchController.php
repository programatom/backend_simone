<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Entrega;

class SearchController extends Controller
{
  public function user_search(Request $request)
      {

        $users = DB::table('users')
                            ->where('name', 'like', $request->filtro)
                            ->orWhere('email',"like" ,$request->filtro)
                            ->orWhere('role',"like",$request->filtro)
                            ->get();

        return view("usuarios.index",[
          "usuarios" => $users
          ]);
      }

  public function entregas_search(Request $request){


        $entregas = DB::table("entregas")->join('pedidos', 'entregas.pedido_id', '=', 'pedidos.id')->select("entregas.*", "pedidos.repartidor_habitual_id", "pedidos.repartidor_excepcional_id");

        if ($request->repartidor_habitual_id != "null") {
            $entregas->where('repartidor_habitual_id', $request->input('repartidor_habitual_id'));
        }

        if ($request->repartidor_excepcional_id != "null") {
            $entregas->where('repartidor_excepcional_id', $request->input('repartidor_excepcional_id'));
        }

        if ($request->fecha_de_entrega_potencial != null) {
            $entregas->where('fecha_de_entrega_potencial', $request->input('fecha_de_entrega_potencial'));
        }

        if ($request->fecha_de_procesamiento_real != null) {
            $entregas->where('fecha_de_procesamiento_real', $request->input('fecha_de_procesamiento_real'));
        }

        if ($request->estado != null) {
            $entregas->where('estado', $request->input('estado'));
        }
        $request->flash();

        session("entregas_search" => $entregas->paginate(100));
        
        return view("entregas.index", [
          "entregas" => $entregas->paginate(100),
          "empleados" => User::where("role" , "empleado")->get()
        ]);

  }

  public function pedidos_search(){

  }
}
