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
          <a class="btn btn-primary btn-block" href="/productos/create">
              Agregar un producto nuevo
          </a>
        </div>
      </div>
      <div class="row justify-content-center">
        @foreach ($productos as $producto)
          <div class="col-md-6">
              <div class="card">
                  <div class="card-header">{{$producto->nombre}}</div>

                  <div class="card-body">
                    <strong>Precio: </strong>{{$producto->precio}} <br>
                    <strong>Descripcion: </strong>{{$producto->descripcion}} <br>
                    <strong>Posición: </strong>{{$producto->posicion}} <br>
                    <a class="btn btn-success btn-block" href="/productos/{{$producto->id}}/edit">
                      Editar
                    </a>
                  </div>
              </div>
          </div>
          @endforeach
      </div>
  </div>
@endsection
