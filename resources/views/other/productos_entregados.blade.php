@extends('layouts.app')

@section('content')
  <div class="container">
      <div class="row justify-content-center">
        <div class="col-12" style="padding:0">
          <form action="/productos_entregados_search" method="POST">
            @csrf
            <div class="row justify-content-center">
              <div class="col-8">
                <input class="form-control" type="text" name ="filtro" placeholder="Filtro" aria-label="Search" value="{{old('filtro')}}">
              </div>
              <div class="col-2 mb-3">
                <button type="submit" class="btn btn-primary">Buscar</button>
              </div>
            </div>
          </form>
        </div>

        <table class="table">
          <thead>
            <tr>
              <th scope="col">#</th>
              <th scope="col">Nº de entrega</th>
              <th scope="col">Cantidad</th>
              <th scope="col">Producto</th>
              <th scope="col">Precio</th>
              <th scope="col">Fecha de entrega</th>

            </tr>
          </thead>
          <tbody>
            @foreach ($productos as $producto)
            <tr>
              <th scope="row">{{$producto->id}}</th>
              <td>{{$producto->entrega_id}}</td>
              <td>{{$producto->cantidad}}</td>
              <td>{{$producto->nombre}}</td>
              <td>{{$producto->precio}}</td>
              <td>{{$producto->fecha_de_entrega}}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="row justify-content-center mb-3">
        {{$productos->links()}}
      </div>
  </div>
@endsection
