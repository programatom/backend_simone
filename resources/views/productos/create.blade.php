@extends('layouts.app')

@section('content')
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-5">

        <form action="/productos" method="POST">
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

              <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="nombre" class="form-control" value="{{ old('nombre') }}"></input>
              </div>
              <div class="form-group">
                <label>Precio</label>
                <input type="number" name="precio" class="form-control" value="{{ old('precio') }}"></input>
              </div>
              <div class="form-group">
                <label>Posicion</label>
                <input type="posicion" name="posicion" class="form-control" value="{{ old('posicion') }}"></input>
              </div>
              <div class="form-group">
                <label>Descripción</label>
                <input type="text" name="descripcion" class="form-control" value="{{ old('descripcion') }}"></input>
              </div>
              <div class="col-12 col-lg-12">
                <button type="submit" class="btn btn-primary btn-block">Crear producto</button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection
