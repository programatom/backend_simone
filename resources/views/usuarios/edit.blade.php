@extends('layouts.app')

@section('content')
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-6">
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
    <div class="row justify-content-center">
      <div class="col-6">
        <div class="card">
          <div class="card-body">

            <form action="/usuarios/{{ $usuario->id }}" method="POST">
              @csrf
              {{ method_field("PATCH")}}

          <div class="form-group">
            <label>Nombre</label>
            <input type="text" name="name" class="form-control" value="{{$usuario->name}}"></input>
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="{{$usuario->email}}"></input>
          </div>
          <div class="form-group">
            <label>Contraseña</label>
            <input type="password" name="password" class="form-control" value="SIMONE"></input>
          </div>
          <input type="hidden" name="web" class="form-control" value="true"></input>

          <div class="col-12 col-lg-12">
            <button type="submit" class="btn btn-success btn-block">Editar usuario</button>
          </div>
        </form>
          </div>
        </div>
      </div>
      <div class="col-6">
        @if ($role_obj->has_role)
          @if($role_obj->role == "particular")
            @component("usuarios.components.particular", [
              "particular" => $particular
            ])
            @endcomponent
          @else
            @component("usuarios.components.empresa", [
              "empresa" => $empresa
            ])
            @endcomponent
          @endif
        @endif
      </div>
    </div>
  </div>
@endsection
