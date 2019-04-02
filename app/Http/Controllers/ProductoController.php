<?php

namespace App\Http\Controllers;

use App\Producto;
use App\ElementoConImage;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

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
      foreach($productos as $producto){
        $imagenes = $producto->imagenes()->get();
        $producto->imagenes = $imagenes;
      }

      return response()->json([
        'status' => 'success',
        'data' =>$productos
      ], 200);
    }

    public function indexFull(){
      $user = Auth::user();

      if($user->role != "admin"){
        return response()->json([
          'status' => 'fail',
          'details' => 'Sin autorización'
      ], 403);
      }else{

        $productos = Producto::all();

        foreach ($productos as $producto) {

            $pedidos = $producto->pedidos()->get();
            foreach($pedidos as $pedido){

              $entregas = $pedido->entregas();
              $user = $pedido->user()->get();

              $pedido->user = $user;
              $pedido->entregas = $entregas;

            }
            $producto->pedidos = $pedidos;
        }

        return response()->json([
            'status' => 'success',
            'data' =>$productos
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
      $messages = [
          'required' => 'El campo :attribute es requerido',
          "integer"=> 'El campo precio debe ser un numero',
          "unique"=> 'Ya hay un producto con esa posicion'
      ];

      // LLEGA UN OBJECTO {producto ; imagenes}
      // CUANDO CARGA UNA IMAGEN EN EL SERVIDOR, SE GUARDA EN LA BD DE IMAGES
      // ESTO SUCEDE PREVIO A ESTO. DESPUES, SE ENVÍA UN ARRAY DE IMAGENES Y UN OBJETO PRODUCTO.


      $user = Auth::user();

     if ($user->role != "admin"){
       return response()->json([
           'status' => 'Sin autorización',
       ], 403);
     }else{
       $producto = (array) $request->producto;
       $validator = Validator::make($producto, [
         'nombre' => 'required',
         'precio' => 'required|integer',
         'descripcion' => 'required',
         "posicion" =>"required|unique:productos"
       ], $messages);

       if ($validator->fails()){
         return response()->json([
             'status' => 'fail',
             "data"=> $validator->errors()
         ], 200);
       }

       $producto = Producto::create($producto);
       $id_producto = $producto->id;
       $imagenes = $request->imagenes;



       foreach($imagenes as $imagen){
         $imagen = (object) $imagen;
         $imagen_id = $imagen->id;
         $rol = $imagen->rol;

         ElementoConImage::create([
           "producto_id" => $id_producto,
           "imagen_id" => $imagen_id,
           "rol" => $rol
         ]);
       }

       return response()->json([
           'status' => 'success',
           'data' => $producto
       ], 200);
     }
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Producto  $producto
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

      $producto = Producto::where("id" , $id)->get()[0];
      $producto->imagenes = $producto->imagenes()->get();

      return response()->json([
          'status' => 'success',
          'data' => $producto
      ], 200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Producto  $producto
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Producto $producto)
    {
      $messages = [
          'required' => 'El campo :attribute es requerido',
          "integer"=> 'El campo precio debe ser un numero',
          "unique"=> 'Ya hay un producto con esa posicion'
      ];

      $user = Auth::user();

      if($user->role != "admin"){
        return response()->json([
          'status' => 'fail',
          'details' => 'Sin autorización'
      ], 403);
      }else{

        $producto_object = (object) $request->producto;
        $producto_id = $producto_object->id;
        $producto_query = Producto::where('id', $producto_id);
        $producto_guardado = $producto_query->get();

        $posicion_producto_guardado = $producto_guardado[0]->posicion;

        $array_validacion = [
          'nombre' => 'required',
          'precio' => 'required|integer',
          'descripcion' => 'required',
          'posicion' => "required"
        ];

        if($producto_object->posicion != $posicion_producto_guardado){
          // Tengo que agregar la validacion de unique en el campo posicion
          $array_validacion["posicion"] = "required|unique:productos";
        }

        $validator = Validator::make((array) $request->producto, $array_validacion , $messages);

        if ($validator->fails()){
          return response()->json([
            'status' => 'fail',
            "data"=> $validator->errors()
          ], 200);
        }
        
        $producto_query->update([
          "nombre" => $producto_object->nombre,
          "precio" => $producto_object->precio,
          "descripcion" => $producto_object->descripcion,
          "posicion" => $producto_object->posicion
        ]);


        ElementoConImage::where([
          "producto_id" => $producto_id
        ])->delete();

        $imagenes = (array) $request->imagenes;


        foreach($imagenes as $imagen){
          $imagen = (object) $imagen;
          $imagen->pivot = (object) $imagen->pivot;

          $id = $imagen->id;
          $rol = $imagen->pivot->rol;
          ElementoConImage::create([
            "producto_id" => $producto_id,
            "imagen_id" => $id,
            "rol" => $rol
          ]);
        }

        return response()->json([
          'status' => 'success',
          'data' => $imagenes
        ], 200);
      }
    }
}
