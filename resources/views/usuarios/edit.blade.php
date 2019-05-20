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
      @if ($role_obj->has_role)
      <div class="col-6">
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
      </div>
      <div class="col-12 mb-2 mt-3" style="padding:0">
        <form action="/productos_entregados_search" method="POST">
          @csrf
          <div class="row justify-content-center">
            <div class="col-8">
              <input class="form-control" type="text" name ="filtro" placeholder="Filtro" aria-label="Search">
            </div>
            <input type="hidden" name="usuario_exception_id" value="{{$usuario->id}}">
            <div class="col-2">
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
            <th scope="col">Fecha</th>

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
            <td>{{$producto->created_at}}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
      @endif

    </div>
  </div>
@endsection
