@extends('layouts.app')

@section('content')
<?php if (session("entregas_search")): ?>
      $entregas = session("entregas_search")
<?php endif; ?>
  <div class="container">
    @if (session('success'))
    <div class="row justify-content-center">
    <div class="col-12 col-lg-12">
      <div class="alert alert-success">
        {{ session('success') }}
      </div>
    </div>
    </div>
    @endif

    @if (session('error'))
    <div class="row justify-content-center">
    <div class="col-12 col-lg-12">

      <div class="alert alert-error">
        {{ session('error') }}
      </div>
    </div>
    </div>
    @endif
      <div class="row justify-content-center mb-2">
        <div class="col-md-6">
          <a class="btn btn-primary btn-block" href="/entregas/create">
              Crear una entrega nueva
          </a>
        </div>
      </div>
  <div class="row justify-content-center">
    <div class="col-12">
      <form action="/entregas_search" method="POST">
        @csrf
        <div class="row justify-content-center">
          <div class="form-group">
            <label>Repartidor Habitual</label>
            <select name = "repartidor_habitual_id" class="browser-default custom-select">
              <option value="null"></option>
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
            <select name = "repartidor_excepcional_id" class="browser-default custom-select">
              <option value="null"></option>
              @foreach($empleados as $empleado)
                @if(old("repartidor_excepcional_id") == $empleado->id)
                  <option selected value="{{$empleado->id}}">{{$empleado->name}}</option>
                  @else
                  <option value="{{$empleado->id}}">{{$empleado->name}}</option>
                @endif
              @endforeach
            </select>
          </div>

          <div class="col-2">
            <label>Entrega potencial</label>
            <input class="form-control" type="text" name ="fecha_de_entrega_potencial" value="{{old('fecha_de_entrega_potencial')}}">
          </div>
          <div class="col-2">
            <label>Procesam. real</label>
            <input class="form-control" type="text" name ="{{old('fecha_de_procesamiento_real')}}">
          </div>
          <div class="col-2">
            <label>Estado</label>
            <input class="form-control" type="text" name ="{{old('estado')}}">
          </div>
          <div class="col-2 mt-3">
            <button type="submit" class="btn btn-primary btn-block mb-2">Buscar</button>
          </div>
        </div>
      </form>
    </div>
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          Todas las entregas
          <div style="float:right">
            <strong>Hoy: </strong>
            <?php
                  $tz = 'America/Argentina/Buenos_Aires';
                  $timestamp = time();
                  $dt = new \DateTime("now", new \DateTimeZone($tz));
                  $hoy = $dt->format('Y/m/d');
                  echo($hoy);?>
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
                <th scope="col">E. adelantadas</th>
                <th scope="col">R. Habitual</th>
                <th scope="col">Estado</th>
                <th scope="col">Fuera de agenda</th>
                <th scope="col">Observaciones</th>
                <th scope="col">Pedido Nº</th>
                <th scope="col">*</th>


              </tr>
            </thead>
            <tbody>
              @foreach($entregas as $entrega)
              <tr>
                <th scope="row">{{$entrega->id}}</th>
                <td>{{$entrega->fecha_de_entrega_potencial}}</td>
                <td>{{$entrega->fecha_de_procesamiento_real}}</td>
                <td>{{$entrega->paga_con}}</td>
                <td>{{$entrega->adelanta}}</td>
                <td>{{$entrega->entregas_adelantadas}}</td>
                <td>{{User::find($entrega->repartidor_habitual_id)->name}}</td>
                <td>{{$entrega->estado}}</td>
                <td>
                  <?php
                  if( $entrega->out_of_schedule == 1)
                  {
                    echo("Si");
                  }else{
                    echo("No");
                  }
                  ?>
                </td>
                <td>
                  <form class="" action="entregas/{{$entrega->id}}" method="POST">
                    @csrf
                    {{method_field("PATCH")}}
                    <textarea type="text" name="observaciones">{{$entrega->observaciones}}</textarea>
                    <button type="submit" class="btn btn-sm btn-success btn-block">Editar</button>
                  </form>
              </td>
              <td>
                {{$entrega->pedido_id}}
              </td>
                <td>
                  @if($entrega->estado == "sin procesar")
                  <form action="/entregas/{{$entrega->id}}" method="POST">
                    @csrf
                    {{ method_field("PATCH")}}
                    <input type="hidden" name="estado" value="cancelada">
                    <button type="submit" class="btn btn-danger btn-block btn-sm">Cancelar</button>
                  </form>
                  @if($entrega->derivada == 1)
                  <form action="/entregas/{{$entrega->id}}" method="POST">
                    @csrf
                    {{ method_field("PATCH")}}
                    <input type="hidden" name="derivada" value="0">
                    <button type="submit" class="btn btn-primary btn-block btn-sm">Retornar</button>
                  </form>
                  @else
                  <form action="/entregas/{{$entrega->id}}" method="POST">
                    @csrf
                    {{ method_field("PATCH")}}
                    <input type="hidden" name="derivada" value="1">
                    <button type="submit" class="btn btn-success btn-block btn-sm">Derivar</button>
                  </form>
                  @endif
                  @else
                  @endif
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div class="row justify-content-center mb-3">
          {{$entregas->links()}}
        </div>
      </div>
    </div>

  </div>
</div>
@endsection
