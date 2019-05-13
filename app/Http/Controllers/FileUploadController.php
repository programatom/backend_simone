<?php

namespace App\Http\Controllers;

use App\Remate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Filesystem\Filesystem;
use App\Http\Controllers\FileBrowserController;

class FileUploadController extends Controller
{


  public function directory_logic($path){
    $existe = file_exists($path);
    if($existe){
      return;
    }else{
      $make_dir = File::makeDirectory($path, $mode = 0777, true, true);
      return;
    }
  }

  public function eliminateAllFilesInDirectory($path){
    $existe = file_exists($path);

    if($existe){
      $file = new Filesystem;
      $file->cleanDirectory($path);
    }
    return;
  }

  public function delete_photo(Request $request){
    $path = $request->path;
    $session_key = $request->session_key;
    $existe = file_exists($path);
    if($existe){
      $file = new Filesystem;
      $file->delete($path);
    }
    return redirect(url()->previous().'#'.$session_key)->with($session_key, "Se borró la foto con éxito");;
  }


  public function store($path, Request $request, $session_key, $session_message)

  {

      if($session_key == "video"){
        $this->validate($request, [
                'filename' => 'required|mimes:mp4,mov|max:20000',
        ]);
      }else{
        $this->validate($request, [

          'filename' => 'required',
          'filename.*' => 'mimes:jpeg,png,jpg,gif,svg,mp4,mov|max:20000'

        ],[
          "required" => "Debe ingresar un archivo"
        ]);
      }


      if($request->hasfile('filename'))
       {

          foreach($request->file('filename') as $image)
          {
              $this->directory_logic($path);
              $name = str_replace(" ", "_", $image->getClientOriginalName());
              $image->move($path, $name);
              $data[] = $name;
          }
       }

      return redirect(url()->previous().'#'.$session_key)->with($session_key, $session_message);
  }


  public function video($path, Request $request, $session_key, $session_message)
  {

    $this->validate($request, [
            'video' => 'required|mimes:mp4,mov|max:20000',
    ]);

    $this->directory_logic($path);
    $video = $request->file('video');
    $name = $video->getClientOriginalName();
    $upload = $video->move($path, $name);

    return back()->with($session_key, $upload);
  }

  public function upload_photo(Request $request){
    $path = $request->path;
    $path = storage_path('app/public/'.$path);
    $this->directory_logic($path);

    if(isset($request->clean_dir)){
      $this->eliminateAllFilesInDirectory($path);
    }

    return $this->store($path, $request, $request->session, $request->session_msg);

  }



}
