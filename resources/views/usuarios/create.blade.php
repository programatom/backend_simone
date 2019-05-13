@extends('layouts.app')

@section('content')
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-5">
        <div class="card">
          <div class="card-body">
            <form action="/usuarios" method="POST">
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
            <label>Nombre</label>
            <input type="text" name="name" class="form-control"></input>
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control"></input>
          </div>
          <div class="form-group">
            <label>Contraseña</label>
            <input type="password" name="password" class="form-control" value="SIMONE"></input>
          </div>
          <div class="form-group">
            <label>Rol</label>
            <select name = "role" class="browser-default custom-select" value="particular">
              <option value="particular">Particular</option>
              <option value="empresa">Empresa</option>
            </select>
            <small class="form-text text-muted">Recuerde que luego de ingresado el rol no se puede modificar</small>
          </div>
          <input type="hidden" name="web" class="form-control" value="true"></input>

          <div class="col-12 col-lg-12">
            <button type="submit" class="btn btn-primary btn-block">Crear usuario</button>
          </div>
        </form>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
