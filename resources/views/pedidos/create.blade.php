@extends('layouts.app')

@section('content')
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12">

        <form id="pedido_form" action="/pedidos" method="POST">
          @csrf
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
                    <select name = "user_id" class="browser-default custom-select">
                      @foreach($users as $user)
                      @if(old("user_id") == $user->id)
                        <option selected value="{{$user->id}}">{{$user->name}}</option>
                        @else
                        <option value="{{$user->id}}">{{$user->name}}</option>
                      @endif
                      @endforeach
                    </select>
                  </div>

                  <div class="form-group">
                    <label>Periodicidad</label>
                    <select class="browser-default custom-select" name="periodicidad">
                      @foreach($periodicidad_select as $select)
                        @if (old('periodicidad') == $select)
                        <option value="{{ $select }}" selected>{{ $select }}</option>
                        @else
                        <option value="{{ $select }}">{{ $select }}</option>
                        @endif
                      @endforeach
                    </select>
                  </div>

                  <div class="form-group">
                    <label>Descuento</label>
                    <input type="number" name="descuento" class="form-control" value="{{ old('descuento') }}"></input>
                  </div>

                  <div class="form-group">
                    <label>Repartidor Habitual</label>
                    <select id="selectHabitual" onchange="checkEmpleado(this)" name = "repartidor_habitual_id" class="browser-default custom-select" >
                      @foreach($empleados as $empleado)
                      @if(old("repartidor_habitual_id") == $empleado->id)
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
                        @if(old("repartidor_excepcional_id") == $empleado->id)
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
                      <option value="en proceso">En proceso</option>
                      <option value="discontinuado">Discontinuado</option>
                    </select>
                  </div>

                </div>
                <div class="col-6">
                  <div class="form-group">
                    <label>Día de entrega</label>
                    <select name = "dia_de_entrega" class="browser-default custom-select">
                      @foreach ($dias_de_entrega as $key => $value)
                        @if(old("dia_de_entrega") == $key)
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
                       @if(old("forma_de_pago") == $key)
                         <option selected value="{{$key}}">{{$key}}</option>
                         @else
                         <option value="{{$key}}">{{$key}}</option>
                       @endif
                      @endforeach
                    </select>
                  </div>

                  <div class="form-group">
                    <label>Fecha de expiración del descuento</label>
                    <input type="text" name="expiracion_descuento" class="form-control" value="{{ old('expiracion_descuento') }}"></input>
                  </div>
                </div>
              </div>


              <div class="col-12 col-lg-12">
                <button class="btn btn-primary btn-block" id="submitPedido">Crear pedido</button>
              </div>
            </div>
          </div>
        </form>
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
