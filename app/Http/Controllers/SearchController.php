<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Entrega;
use App\Http\Controllers\UserController;
class SearchController extends Controller
{
  public function user_search(Request $request)
      {
        if($request->filtro == null){
          return redirect("usuarios");
        }
        $users = DB::table('users')
                            ->where('name', 'like', $request->filtro)
                            ->orWhere('email',"like" ,$request->filtro)
                            ->orWhere('role',"like",$request->filtro)
                            ->orWhere('id',"=",$request->filtro)
                            ->paginate(50);
                            $request->flash();

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
            $entregas->where('entregas.estado', $request->input('estado'));
        }
        $request->flash();

        return view("entregas.index", [
          "entregas" => $entregas->paginate(100),
          "empleados" => User::where("role" , "empleado")->get()
        ]);

  }

  public function pedidos_search(Request $request){
      if($request->filtro == null){
        return redirect("pedidos");
      }
      $pedidos = DB::table('pedidos')
                          ->where('periodicidad', 'like', $request->filtro)
                          ->orWhere('forma_de_pago',"like" ,$request->filtro)
                          ->orWhere('estado',"like",$request->filtro)
                          ->orWhere('dia_de_entrega',"like",$request->filtro)
                          ->orWhere('descuento',"like",$request->filtro)
                          ->orWhere('user_id',"=",$request->filtro)
                          ->paginate(50);
                          $request->flash();


      return view("pedidos.index",[
        "pedidos" => $pedidos
        ]);
  }

  public function productos_entregados_search(Request $request){

    if($request->filtro == null){
      if(isset($request->usuario_exception_id)){
        return redirect("usuarios/".$request->usuario_exception_id.'/edit');
      }else{
        return redirect("productos_entregados");
      }
    }
    $productos = DB::table('producto_entregas')
                        ->where('entrega_id', 'like', $request->filtro)
                        ->orWhere('nombre',"like" ,$request->filtro)
                        ->orWhere('precio',"like" ,$request->filtro)
                        ->orWhere('cantidad',"like" ,$request->filtro)
                        ->orWhere('fecha_de_entrega',"like" ,$request->filtro)

                        ->paginate(50);
                        $request->flash();

    if(isset($request->usuario_exception_id)){
      $user_controller = new UserController();
      $user_data = $user_controller->get_user_data($request->usuario_exception_id);

      return view("usuarios.edit",[
        "usuario" => $user_data->usuario,
        "role_obj" => $user_data->role_obj,
        "particular" => $user_data->role_data,
        "empresa" => $user_data->role_data,
        "productos" => $productos
      ]);

    }else{
      return view("other.productos_entregados",[
        "productos" => $productos
      ]);
    }
  }
}
