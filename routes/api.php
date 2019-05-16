<?php

use Illuminate\Http\Request;

// ELIMINAR TIPO DE PEDIDO DE LA TABLA DE PEDIDOS

//

// LOS UNICOS PUNTOS CRITICOS CON LAS ENTREGAS Y LOS PEDIDOS

Route::post('logout', 'Auth\LoginController@logout');
Route::post("register", 'Auth\RegisterController@register');
Route::post("login", 'Auth\LoginController@login');


// RUTAS WEB

Route::get('productos', 'ProductoController@index_api'); //WORKS
Route::post('verificarCupon', 'CuponController@verify_coupon'); // WORKS
Route::post('muestrasGratis', 'MuestrasPedidoController@store'); // WORKS
Route::post('ingresarPedido', 'PedidoController@store'); //WORKS
Route::post('ingresarDatosEmpresa', 'EmpresaController@update_web');
Route::post('ingresarDatosParticular', 'ParticularController@update_web');


// 12- CronJob
Route::get("cron", "cronEmisionEntregaController@iniciar_proceso_cron");
Route::post("reestablecerPedido", "cronEmisionEntregaController@reestablecer_estado_en_proceso");

Route::group(['middleware' => 'auth:api'], function() {


    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // 3- Pedidos

    Route::get('pedidosShow', 'PedidoController@show');
    Route::get('pedidoShowAdmin/{id}', 'PedidoController@showAdmin');
    Route::post('pedidoStoreAdmin', 'PedidoController@storeAdmin'); // LISTO
    Route::post('pedidoUpdate', 'PedidoController@updateAdmin'); // LISTO
    Route::get('pedidosEmpleado', 'PedidoController@get_where_empleado'); // LISTO
    Route::post('pedidoWhere', 'PedidoController@get_where');


    // 4- Entregas

    Route::get('entregas', 'EntregaController@index'); // Entrega con user y pedido
    Route::get('entregaShow', 'EntregaController@show'); // Entrega de un user y de un pedido
    Route::get('entregasEmpleadoHabituales', 'EntregaController@get_entregas_habituales_empleado_hoy'); // PROBADO

    Route::post('entrega', 'EntregaController@store_api'); // PROBADO
    Route::post('entregaUpdate', 'EntregaController@update');
    Route::post("procesarEntrega" , 'EntregaController@procesar_entrega');
    Route::post("buscarEntregasFecha" , 'EntregaController@get_from_date_to');
    Route::get("entregasAlarmaYExcepcionales" , 'EntregaController@get_entregas_con_alarma_y_excepcionales');
    Route::get("entregasDanger", "EntregaController@get_entregas_danger");

});
