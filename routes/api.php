<?php

use Illuminate\Http\Request;

// ELIMINAR TIPO DE PEDIDO DE LA TABLA DE PEDIDOS

//

// LOS UNICOS PUNTOS CRITICOS CON LAS ENTREGAS Y LOS PEDIDOS

Route::post('logout', 'Auth\LoginController@logout');
Route::post("register", 'Auth\RegisterController@register');
Route::post("login", 'Auth\LoginController@login');


// RUTAS WEB

Route::get('productos', 'ProductoController@index'); //WORKS
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

    // 1 - Particulares
    Route::get('particulares', 'ParticularController@index');
    Route::get('particularesShow', 'ParticularController@show');
    Route::post('particularesUpdate', 'ParticularController@update');

    // 2- Empresas
    Route::get('empresas', 'EmpresaController@index');
    Route::get('empresasShow', 'EmpresaController@show');
    Route::post('empresasUpdate', 'EmpresaController@update');

    // 3- Pedidos

    Route::get('pedidos/{filter}', 'PedidoController@index');
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

    Route::post('entrega', 'EntregaController@store'); // PROBADO
    Route::post('entregaUpdate', 'EntregaController@update');
    Route::post("procesarEntrega" , 'EntregaController@procesar_entrega');
    Route::post("buscarEntregasFecha" , 'EntregaController@get_from_date_to');
    Route::get("entregasAlarmaYExcepcionales" , 'EntregaController@get_entregas_con_alarma_y_excepcionales');
    Route::get("entregasDanger", "EntregaController@get_entregas_danger");

    // 5- Productos
    Route::get('productos', 'ProductoController@index_api');
    Route::get('productosFull', 'ProductoController@indexFull');
    Route::get('productoShow/{id}', 'ProductoController@show');
    Route::post('crearProducto', 'ProductoController@store');
    Route::post('modificarProducto', 'ProductoController@update');

    // 6- Imagenes
    Route::get('imagenes', 'ImageController@index');
    Route::post('imagen', 'ImageController@store');
    Route::put( "guardarImagen" , 'ImageController@guardarImagen');
    Route::post( "eliminarImagen" , 'ImageController@eliminarImagen');

    // 7- Cupones
    Route::get('cupones', 'CuponController@index');
    Route::post('cuponesStore', 'CuponController@store');
    Route::post('cuponesDelete', 'CuponController@delete');

    // 9- Empleados
    Route::post('empleadoUpdate', 'EmpleadoController@update');
    Route::get('empleados', 'EmpleadoController@index');

    // 10- Usuarios
    Route::get('usuarios', 'UserController@index');
    Route::get('usuarioShowAdmin/{id}', 'UserController@showAdmin');
    Route::post('usuarioUpdate', 'UserController@update');

    // 11- Muestras Pedido

    Route::get('muestrasPedido' , "MuestrasPedidoController@index");
    Route::get('muestrasPedidoSinVer' , "MuestrasPedidoController@buscar_pedidos_de_muestra_sin_ver");
    Route::get('muestrasPedidoUpdate' , "MuestrasPedidoController@update");



});
