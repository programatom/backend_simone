@extends('layouts.app')

@section('content')
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-5">
        <div class="card">
          <div class="card-body">
            <form action="/cupones" method="POST">
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
              <div class="form-group">
                <label>Código</label>
                <input type="text" name="codigo" class="form-control" value="{{ old('codigo') }}"></input>
              </div>
              <div class="form-group">
                <label>Fecha de expiración</label>
                <input type="text" name="fecha_expiracion" class="form-control" aria-aria-describedby="fecha_expiracion" value="{{ old('fecha_expiracion') }}"></input>
                <small id="fecha_expiracion" class="form-text text-muted">Formato: AÑO/MES/DIA ej. 2019/01/01</small>
              </div>
              <div class="form-group">
                <label>Porcentaje de descuento</label>
                <input type="number" name="porcentaje_descuento" class="form-control" value="{{ old('porcentaje_descuento') }}"></input>
              </div>
              <div class="col-12 col-lg-12">
                <button type="submit" class="btn btn-primary btn-block">Crear cupón</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
