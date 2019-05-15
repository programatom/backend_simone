<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
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
Route::get('/check_day', "cronEmisionEntregaController@get_next_or_previous_date_with_this_day");
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

Route::resource("pedidos" ,"PedidoController")->middleware('auth');
