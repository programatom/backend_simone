@extends('layouts.app')

@section('content')
  <div class="container">
    <div class="row justify-content-center">
      <form action="/productos/{{ $producto->id }}" method="POST">
        @csrf
        {{ method_field("PATCH")}}
        <div class="card">
          <div class="card-body">
            <div class="row">
              <div class="col-12 col-lg-12">
                @if ($errors->any())
                <div class="alert alert-danger">
                  <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                  </ul>
                </div>
                @endif
              </div>
            </div>
            <div class="form-group">
              <label>Nombre</label>
              <input type="text" name="nombre" class="form-control" value="{{ $producto->nombre }}"></input>
            </div>
            <div class="form-group">
              <label>Precio</label>
              <input type="number" name="precio" class="form-control" value="{{ $producto->precio }}"></input>
            </div>
            <div class="form-group">
              <label>Posicion</label>
              <input type="posicion" name="posicion" class="form-control" value="{{ $producto->posicion }}"></input>
            </div>
            <div class="form-group">
              <label>Descripción</label>
              <input type="text" name="descripcion" class="form-control" value="{{ $producto->descripcion }}"></input>
            </div>
            <div class="col-12 col-lg-12">
              <button type="submit" class="btn btn-success btn-block">Editar producto</button>
            </div>
          </form>
          <form id="logout-form" action="/productos/{{$producto->id}}" method="POST" style="display: none;">
            @csrf
            {{ method_field("DELETE")}}
          </form>
          </div>
        </div>

      <div class="col-12 col-lg-6">
      <div class="card">
        <div class="card-header">
          Imagen
        </div>
        <div class="card-body">
          @if (session('imagen_principal'))
          <div class="row">
            <div class="col-12 col-lg-12">
              <div class="alert alert-success">
                {{ session('imagen_principal') }}
              </div>
            </div>
          </div>
          @endif
          <form method="POST" action="{{url('upload_photo')}}" enctype="multipart/form-data">
            {{csrf_field()}}
            <input type="hidden" name="path" value="{{'productos/'.$producto->id.'/imagen_principal'}}">

            <input type="hidden" name="session" value="imagen_principal">

            <input type="hidden" name="session_msg" value="Se cargó la imagen con éxito">
            <input type="hidden" name="clean_dir" value="1">


            <div class="form-group control-group" >
              <input type="file" name="filename[]" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top:10px">Cargar imagen</button>
          </form>
          @foreach($imagen_principal as $imagen)
          <img src="{{$imagen}}" height="320">
          @endforeach
        </div>
      </div>
    </div>
    </div>
  </div>
@endsection
