@extends('layouts.app')

@section('content')
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
        <a class="btn btn-primary btn-block" href="/pedidos/create">
            Agregar un pedido nuevo
        </a>
      </div>
    </div>
    <div class="row justify-content-center">
      <table class="table">
        <thead>
          <tr>
            <th scope="col">#</th>

            <th scope="col">Descuento</th>
            <th scope="col">Periodicidad</th>
            <th scope="col">Repartidor_habitual_id</th>
            <th scope="col">Repartidor_excepcional_id</th>
            <th scope="col">Estado</th>
            <th scope="col">Dia_de_entrega</th>
            <th scope="col">Forma_de_pago</th>
            <th scope="col">Expiracion_descuento</th>

            <th scope="col">*</th>

          </tr>
        </thead>
        <tbody>
          @foreach ($pedidos as $pedido)
          <tr>
            <th scope="row">{{$pedido->id}}</th>
            <td>{{$pedido->descuento}}</td>
            <td>{{$pedido->periodicidad}}</td>
            <td>{{User::find($pedido->repartidor_habitual_id)->name}}</td>
            <td>{{User::find($pedido->repartidor_excepcional_id)->name}}</td>
            <td>{{$pedido->estado}}</td>
            <td><?php
            $dias_de_entrega = array(
              "1" => "Lunes",
              "2" => "Martes",
              "3" => "Miercoles",
              "4" => "Jueves",
              "5" => "Viernes",
              "6" => "Sabado",
              "7" => "Domingo",
            );

            echo($dias_de_entrega[$pedido->dia_de_entrega]);?>
            </td>
            <td>{{$pedido->forma_de_pago}}</td>
            <td>{{$pedido->expiracion_descuento}}</td>

            <td><a class="btn btn-success btn-sm" href="/pedidos/{{$pedido->id}}/edit">
                        Ver más
            </a></td>
          </tr>
          @endforeach
        </tbody>
    </div>
</div>
@endsection
