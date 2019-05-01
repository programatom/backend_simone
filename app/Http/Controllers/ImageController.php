<?php

namespace App\Http\Controllers;

use App\Image;
use App\ElementoConImage;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;


class ImageController extends Controller
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

        $images = Image::all();

        return response()->json([
            'status' => 'success',
            'data' => $images
        ], 200);
      }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */


    public function guardarImagen(Request $request){

      //http://localhost:8000/storage/imagenes/1q8g64OX4ieAKMh5h3fIWe4t4FdoGj19wIqfpS3K.jpeg

      $user = Auth::user();

      if($user->role != "admin"){
        return response()->json([
          'status' => 'Sin autorización'
        ], 403);
      }

      $file = $request->file('imagen');
      $file_name = $file->getClientOriginalName();

      $url = storage_path()."/app/public/imagenes/".$file_name;

      $exists = file_exists( $url);

      if($exists){
        return response()->json([
          'status' => 'fail',
          'data' =>  "Un archivo con este nombre ya está subido"
        ], 200);
      }

      $upload = $file->storeAs("public/imagenes" , $file_name);

      $url = Storage::url($upload);
      $image = Image::create([
        "url" => $url,
        "nombre" => $file_name
      ]);
       return response()->json([
         'status' => 'success',
         'data' =>  $image
       ], 200);
      }

      public function eliminarImagen(Request $request){

      $user = Auth::user();

      if($user->role != "admin"){
        return response()->json([
          'status' => 'Sin autorización'
        ], 403);
      }

      $imagen_utilizada = ElementoConImage::where("imagen_id", $request->id)->get();
      if(count($imagen_utilizada) > 0){
        return response()->json([
          'status' => 'fail',
          'data' => "No se puede eliminar una imagen que esta siendo utilizada por algún producto"
        ], 200);
      }
      $filename = $request->nombre;
      $deletion = File::delete(storage_path()."/app/public/imagenes/".$filename);

      $id_imagen = $request->id;

      Image::where("id",$id_imagen)->delete();

      if($deletion){
        return response()->json([
          'status' => 'success',
          'data' => "Imagen eliminada con éxito"
        ], 200);
      }else{
        return response()->json([
          'status' => 'fail',
          'data' => "Ocurrió algun error con la eliminación de la imagen"
        ], 200);
      }

      }

}
