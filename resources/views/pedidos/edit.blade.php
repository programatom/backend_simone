@extends('layouts.app')

@section('content')

<div class="container">
  <div class="row justify-content-center">
    <div class="col-6">
      <div class="card">
        <div class="card-header">
          Edición de productos
        </div>
        <div class="card-body">
          <form action="/producto_pedido" method="POST">
            @csrf
            <div class="form-group">
              <label>Producto</label>
              <select name = "producto_id" class="browser-default custom-select">
                @foreach($productos as $producto)
                <option value="{{$producto->id}}">{{$producto->nombre}}</option>
                @endforeach
              </select>
            </div>
            <input type="hidden" name="pedido_id" value="{{$pedido->id}}">
            <button type="submit" class="btn btn-success btn-block">Agregar producto</button>
          </form>

          @foreach($pedido->productos as $producto)
          <option value="{{$producto->id}}">{{$producto->nombre}}</option>
          <table class="table">
            <thead>
              <tr>
                <th scope="col">Nombre</th>
                <th scope="col">$ Precio</th>
                <th scope="col">Cantidad</th>
                <th scope="col">*</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($pedido->productos as $producto)
              <tr>
                <td>{{$producto->nombre}}</td>
                <td>{{$producto->precio}}</td>
                <td>{{$producto->cantidad}}</td>
                <td>
                  <form action="/producto_pedido_delete" method="POST">
                    @csrf
                    <input type="hidden" name="producto_id" value="{{$producto->id}}">
                    <input type="hidden" name="pedido_id" value="{{$pedido->id}}">
                    <button class="btn btn-danger btn-sm" type="submit">
                      Eliminar
                    </button>
                  </form>
                </td>
              </tr>
              @endforeach
            </tbody>
            @endforeach
          </table>

        </div>
      </div>
      <div class="card">
        <div class="card-header">
          Datos de estado
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-6">
              <div class="form-group">
                <label>Alarma</label>
                <input type="number" readonly name="alarma" class="form-control" value="{{ $pedido->alarma }}"></input>
                <small class="form-text text-mute">
                  Si el pedido no se procesó pasado el dia de entrega pero aún no pasó un periodo igual a su periodicidad
                </small>
              </div>
            </div>
            <div class="col-6">
              <div class="form-group">
                <label>Danger</label>
                <input type="number" readonly name="danger" class="form-control" value="{{ $pedido->danger }}"></input>
                <small class="form-text text-mute">
                  Si el pedido no se procesó y ya paso un periodo igual a su periodicidad está en estado de danger
                </small>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-6">
              <div class="form-group">
                <label>Fecha de restauración</label>
                <input type="number" readonly name="danger" class="form-control" value="{{ $pedido->fecha_de_restauracion }}"></input>
                <small class="form-text text-mute">
                  Si el pedido está adelantado esta fecha es el dia en donde se tiene que volver a entregar producto al cliente
                </small>
              </div>
            </div>
            <div class="col-6">
              <div class="form-group">
                <label>Dias en danger</label>
                <input type="number" readonly name="danger" class="form-control" value="{{ $pedido->dias_sin_procesar_danger }}"></input>

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-6">
      <div class="card">
        <div class="card-header">
          Edición de datos
        </div>
        <div class="card-body">
          <form action="/pedidos/{{ $pedido->id }}" method="POST">
        @csrf
        {{ method_field("PATCH")}}
        @if ($errors->any())
        <div class="alert alert-danger">
          <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
        @endif
        <div class="card">
          <div class="card-body">

            <div class="row">
              <div class="col-6">
                <div class="form-group">
                  <label>Usuario</label>
                  <select name = "user_id" readonly class="browser-default custom-select">
                    <option selected value="{{$pedido->user_id}}">{{User::find($pedido->user_id)->name}}</option>
                  </select>
                </div>

                <div class="form-group">
                  <label>Periodicidad</label>
                  <select class="browser-default custom-select" name="periodicidad">
                    @foreach($periodicidad_select as $select)
                      @if ($pedido->periodicidad == $select)
                      <option value="{{ $select }}" selected>{{ $select }}</option>
                      @else
                      <option value="{{ $select }}">{{ $select }}</option>
                      @endif
                    @endforeach
                  </select>
                </div>

                <div class="form-group">
                  <label>Descuento</label>
                  <input type="number" name="descuento" class="form-control" value="{{ $pedido->descuento }}"></input>
                </div>

                <div class="form-group">
                  <label>Repartidor Habitual</label>
                  <select id="selectHabitual" onchange="checkEmpleado(this)" name = "repartidor_habitual_id" class="browser-default custom-select" >
                    @foreach($empleados as $empleado)
                    @if($pedido->repartidor_habitual_id == $empleado->id)
                      <option selected value="{{$empleado->id}}">{{$empleado->name}}</option>
                      @else
                      <option value="{{$empleado->id}}">{{$empleado->name}}</option>
                    @endif
                    @endforeach
                  </select>
                </div>

                <div class="form-group">
                  <label>Repartidor Excepcional</label>
                  <select id="selectExcepcional" onchange="checkEmpleado(this)"  name = "repartidor_excepcional_id" class="browser-default custom-select">
                    @foreach($empleados as $empleado)
                      @if($pedido->repartidor_excepcional_id == $empleado->id)
                        <option selected value="{{$empleado->id}}">{{$empleado->name}}</option>
                        @else
                        <option value="{{$empleado->id}}">{{$empleado->name}}</option>
                      @endif
                    @endforeach
                  </select>
                  <small class="form-text text-danger" id="empleadoAlert" style="visibility:hidden;">No se puede elegir el mismo empleado en ambos campos</small>
                </div>

                <div class="form-group">
                  <label>Estado</label>
                  <select name = "estado" class="browser-default custom-select">
                    @if($pedido->estado == "discontinuado")
                    <option value="en proceso">En proceso</option>
                    <option value="discontinuado" selected>Discontinuado</option>
                    @else
                    <option value="en proceso" selected>En proceso</option>
                    <option value="discontinuado">Discontinuado</option>
                    @endif

                  </select>
                </div>

              </div>
              <div class="col-6">
                <form action="/pedido/{{$pedido->id}}" method="POST">
                  @csrf
                  {{ method_field("PATCH")}}
                  <div class="form-group">
                    <label>Día de entrega</label>
                    <select name = "dia_de_entrega" class="browser-default custom-select">
                      @foreach ($dias_de_entrega as $key => $value)
                      @if($pedido->dias_de_entrega == $key)
                      <option selected value="{{$key}}">{{$dias_de_entrega[$key]}}</option>
                      @else
                      <option value="{{$key}}">{{$dias_de_entrega[$key]}}</option>
                      @endif
                      @endforeach
                    </select>
                  </div>

                  <div class="form-group">
                    <label>Forma de pago</label>
                    <select name = "forma_de_pago" class="browser-default custom-select">
                      @foreach ($formas_de_pago as $key)
                      @if($pedido->formas_de_pago == $key)
                      <option selected value="{{$key}}">{{$key}}</option>
                      @else
                      <option value="{{$key}}">{{$key}}</option>
                      @endif
                      @endforeach
                    </select>
                  </div>

                  <div class="form-group">
                    <label>Fecha de expiración del descuento</label>
                    <input type="text" name="expiracion_descuento" class="form-control" value="<?php
                    if(old("expiracion_descuento")){
                      echo(old("expiracion_descuento"));
                    }else{
                      echo($pedido->expiracion_descuento );
                    }
                    ?>"></input>
                  </div>
                </div>

                </form>
            </div>


            <div class="col-12 col-lg-12">
              <button class="btn btn-primary btn-block" id="submitPedido">Guardar pedido</button>
            </div>
          </div>
        </div>
      </form>
        </div>
      </div>
    </div>


  </div>
  <div class="row justify-content-center">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          Entregas del pedido
          <div style="float:right">
            <strong>Hoy: </strong>
            <?php
                  $tz = 'America/Argentina/Buenos_Aires';
                  $timestamp = time();
                  $dt = new \DateTime("now", new \DateTimeZone($tz));
                  $hoy = $dt->format('Y/m/d');
                  echo($hoy); ?>
          </div>
        </div>
        <div class="card-body">
          <table class="table">
            <thead>
              <tr>
                <th scope="col">#</th>
                <th scope="col">Entrega potencial</th>
                <th scope="col">Procesamiento real</th>
                <th scope="col">Pagó con</th>
                <th scope="col">Adelanta</th>
                <th scope="col">Entregas adelantadas</th>
                <th scope="col">Reintentar</th>
                <th scope="col">Estado</th>
                <th scope="col">Observaciones</th>
                <th scope="col">Derivada</th>

              </tr>
            </thead>
            <tbody>
              @foreach($pedido->entregas as $key => $entrega)
              <tr>
                <th scope="row">{{$entrega->id}}</th>
                <td>{{$entrega->fecha_de_entrega_potencial}}</td>
                <td>{{$entrega->fecha_de_procesamiento_real}}</td>
                <td>{{$entrega->paga_con}}</td>
                <td>{{$entrega->adelanta}}</td>
                <td>{{$entrega->entregas_adelantadas}}</td>
                <td>{{$entrega->reintentar}}</td>
                <td>{{$entrega->estado}}</td>
                <td>{{$entrega->observaciones}}</td>
                <td>{{$entrega->derivada}}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>


</div>


<script type="text/javascript">


function checkEmpleado(a) {
  if( document.getElementById("selectExcepcional").value === document.getElementById("selectHabitual").value){
    document.getElementById("empleadoAlert").style["visibility"] = "visible";
    document.getElementById("submitPedido").disabled = true;

  }else{
    document.getElementById("empleadoAlert").style["visibility"] = "hidden";
    document.getElementById("submitPedido").disabled = false;

  }
}
checkEmpleado();
</script>

@endsection
