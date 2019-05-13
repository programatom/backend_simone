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
          <a class="btn btn-primary btn-block" href="/usuarios/create">
              Agregar un usuario nuevo
          </a>
        </div>
      </div>
      <div class="row justify-content-center">
        <table class="table">
          <thead>
            <tr>
              <th scope="col">#</th>
              <th scope="col">Nombre</th>
              <th scope="col">Email</th>
              <th scope="col">Rol</th>
              <th scope="col">*</th>

            </tr>
          </thead>
          <tbody>
            @foreach ($usuarios as $usuario)
            <tr>
              <th scope="row">{{$usuario->id}}</th>
              <td>{{$usuario->name}}</td>
              <td>{{$usuario->email}}</td>
              <td>{{$usuario->role}}</td>
              <td><a class="btn btn-success btn-sm" href="/usuarios/{{$usuario->id}}/edit">
                          Ver más
              </a></td>
            </tr>
            @endforeach
          </tbody>
      </div>
  </div>
@endsection
