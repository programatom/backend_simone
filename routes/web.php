<?php
Route::post("upload_photo", "FileUploadController@upload_photo")->middleware('auth');

Route::get('/', function () {
    return view('home');
})->middleware("auth");

Route::get('/home', function () {
    return view('home');
})->middleware("auth");

Route::get('/simulate', "TestController@simulate");
Route::get('/check_day', "TestController@check_proper_day");
Route::get('/check_pedidos', "TestController@recover_certain_pedidos_with_entregas");
Route::get("cron", "cronEmisionEntregaController@iniciar_proceso_cron");

Auth::routes();

Route::resource("productos" ,"ProductoController")->middleware('auth');
Route::resource("cupones" ,"CuponController")->middleware('auth');
Route::resource("usuarios" ,"UserController")->middleware('auth');
Route::resource("particulares" ,"ParticularController")->middleware('auth');
Route::resource("empresas" ,"EmpresaController")->middleware('auth');
Route::resource("entregas" ,"EntregaController")->middleware('auth');
Route::post("producto_pedido", "PedidoController@producto_pedido")->middleware('auth');
Route::post("producto_pedido_delete", "PedidoController@producto_pedido_delete")->middleware('auth');

Route::post("user_search", "SearchController@user_search")->middleware("auth");
Route::post("entregas_search", "SearchController@entregas_search")->middleware("auth");
Route::post("pedidos_search", "SearchController@pedidos_search")->middleware("auth");
Route::post("productos_entregados_search", "SearchController@productos_entregados_search")->middleware("auth");

Route::get("productos_entregados", function () {

  return view("other.productos_entregados", [
    "productos" => DB::table('producto_entregas')->orderBy('created_at', 'desc')->paginate(50)
  ]);
});
Route::resource("pedidos" ,"PedidoController")->middleware('auth');
