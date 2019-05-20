<?php

namespace App\Http\Controllers;

use App\Producto;
use App\ElementoConImage;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\FileBrowserController;
use App\Rules\Precio;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $productos = Producto::all();
      return view("productos.index",[
        "productos" => $productos
      ]);
    }

    public function index_api()
    {
      $productos = Producto::all();
      return response()->json([
        "status" => "success",
        "data" => $productos
      ],200);
    }

    public function create(){
      return view("productos.create");
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
          'required' => 'El campo :attribute es requerido',
          "integer"=> 'El campo precio debe ser un numero',
          "unique"=> 'Ya hay un producto con esa posicion'
      ];

      $validate = $this->validate($request, [
        'nombre' => 'required',
        'precio' => ['required', "integer", new Precio],
        'descripcion' => 'required',
        "posicion" =>"required|unique:productos|integer"
      ], $messages);

      $request = $request->all();
      unset($request["_token"]);

      Producto::create($request);

      return redirect("productos")->with("success" , "Se creo un nuevo producto con éxito, en la pantalla de edición podrá agregar las imagenes");

    }

    public function edit($id){
      $producto = Producto::findOrFail($id);
      // Acá debería buscar las fotos que tengo, pero lo podría hacer con el file browser.
      $file_browser = new FileBrowserController;
      $path = "productos/".$id.'/';
      $sub_dir_array = array(
        "imagen_principal",
      );
      $response_obj = $file_browser->browse($path, $sub_dir_array);
      // Debería poner una opcion de editar
      if(count($response_obj->imagen_principal)){
        $response_obj->imagen_principal[0] = env("APP_URL")."/storage/".$response_obj->imagen_principal[0];
      }
      return view("productos.edit", [
        "producto" => $producto,
        "imagen_principal" => $response_obj->imagen_principal
      ]);
    }


    public function show($id)
    {

      $producto = Producto::where("id" , $id)->get()[0];
      $producto->imagenes = $producto->imagenes()->get();

      return response()->json([
          'status' => 'success',
          'data' => $producto
      ], 200);
    }

    public function update($id)
    {
      $producto = Producto::findOrFail($id);
      $posicion_enviada = request()->all()["posicion"];
      $validation_array_posicion = array();
      if($producto->posicion == $posicion_enviada){
        $validation_array_posicion = ["required"];
      }else{
        $validation_array_posicion = ["required", "unique:productos"];
      }
      $messages = [
          'required' => 'El campo :attribute es requerido',
          "integer"=> 'El campo precio debe ser un numero',
          "unique"=> 'Ya hay un producto con esa posicion'
      ];
        request()->validate([
          'nombre' => 'required',
          'precio' => ['required', "integer" ,  new Precio],
          'descripcion' => 'required',
          'posicion' => $validation_array_posicion
        ] , $messages);

        $request = request()->all();
        unset($request["_method"]);
        unset($request["_token"]);
        $producto->update($request);
        return redirect("productos")->with("success" , "Se actualizó el producto con éxito");

    }
}
