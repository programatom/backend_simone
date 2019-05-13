<?php

namespace App\Http\Controllers;

use App\Remate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Filesystem\Filesystem;


class FileBrowserController extends Controller
{

  public function file_browser(Request $request)
  {
    // /remates/remate_ID/
    $path = $request->path;
    $sub_dir_array = $request->sub_dir_array;
    $response = $this->browse($path, $sub_dir_array);
    return response()->json([
      "status" => "success",
      "data" => $response
    ]);
  }

  static public function browse($path, $sub_dir_array){
    $file_system = new Filesystem;
    $response_obj = new \stdClass();

    foreach($sub_dir_array as $sub_dir){

      $files = Storage::files($path.$sub_dir);
      if(count($files) != 0){
        $response_obj->$sub_dir = $files;
      }else{
        $response_obj->$sub_dir = [];
      }
    }
    return $response_obj;
  }

}
