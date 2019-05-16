@extends('layouts.app')

@section('content')
<div class="container">
  <div class="row justify-content-center">
    <div class="col-6">
      <div class="card">
        <div class="card-header">
          Emisión de entrega fuera de agenda
          <div style="float:right">
            <strong>Hoy: </strong> <?php
                  $tz = 'America/Argentina/Buenos_Aires';
                  $timestamp = time();
                  $dt = new \DateTime("now", new \DateTimeZone($tz));
                  $hoy = $dt->format('Y/m/d');
                  echo($hoy); ?>
          </div>
        </div>
        <div class="card-body">
          <form action="/entregas" method="POST">
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

        @if (session("errors_custom"))
        <div class="alert alert-danger">
          <ul>
            <li>{{session("errors_custom")}}</li>
          </ul>
        </div>
        @endif

        <div class="form-group">
          <label>Pedido</label>
          <select name = "pedido_id" class="browser-default custom-select">
            @foreach($pedidos as $pedido)
            @if(old("pedido_id") == $pedido->id)
              <option selected value="{{$pedido->id}}">{{$pedido->id}}</option>
              @else
              <option value="{{$pedido->id}}">{{$pedido->id}}</option>
            @endif
            @endforeach
          </select>
        </div>

        <div class="form-group">
          <label>Fecha de entrega potencial</label>
          <input type="text" name="fecha_de_entrega_potencial" class="form-control" value="{{ old('fecha_de_entrega_potencial') }}"></input>
        </div>
        <div class="form-group">
          <label>Observaciones</label>
          <input type="text" name="observaciones" class="form-control" value="{{ old('observaciones') }}"></input>
        </div>
        <input type="hidden" name="out_of_schedule" value="1"></input>

        <button type="submit" class="btn btn-primary btn-block">Emitir entrega</button>
      </form>

        </div>
      </div>
    </div>
  </div>
</div>
@endsection
