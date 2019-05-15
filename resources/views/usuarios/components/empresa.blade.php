<div class="card">
  <div class="card-body">
    <form action="/empresas/{{ $empresa->id }}" method="POST">
          @csrf
          {{ method_field("PATCH")}}


      <div class="form-group">
        <label>razon_social</label>
        <input type="text" name="razon_social" class="form-control" value="{{$empresa->razon_social}}"></input>
      </div>
      <div class="form-group">
        <label>CUIT</label>
        <input type="text" name="CUIT" class="form-control" value="{{$empresa->CUIT}}"></input>
      </div>
      <div class="form-group">
        <label>dom_fiscal</label>
        <input type="text" name="dom_fiscal" class="form-control" value="{{$empresa->dom_fiscal}}"></input>
      </div>
      <div class="form-group">
        <label>telefono</label>
        <input type="text" name="telefono" class="form-control" value="{{$empresa->telefono}}"></input>
      </div>
      <div class="form-group">
        <label>calle</label>
        <input type="text" name="calle" class="form-control" value="{{$empresa->calle}}"></input>
      </div>
      <div class="form-group">
        <label>numero</label>
        <input type="text" name="numero" class="form-control" value="{{$empresa->numero}}"></input>
      </div>
      <div class="form-group">
        <label>piso</label>
        <input type="text" name="piso" class="form-control" value="{{$empresa->piso}}"></input>
      </div>
      <div class="form-group">
        <label>depto</label>
        <input type="text" name="depto" class="form-control" value="{{$empresa->depto}}"></input>
      </div>
      <div class="form-group">
        <label>localidad</label>
        <input type="text" name="localidad" class="form-control" value="{{$empresa->localidad}}"></input>
      </div>
      <div class="form-group">
        <label>provincia</label>
        <input type="text" name="provincia" class="form-control" value="{{$empresa->provincia}}"></input>
      </div>
      <div class="form-group">
        <label>nombre_receptor</label>
        <input type="text" name="nombre_receptor" class="form-control" value="{{$empresa->nombre_receptor}}"></input>
      </div>
      <div class="form-group">
        <label>observaciones</label>
        <input type="text" name="observaciones" class="form-control" value="{{$empresa->observaciones}}"></input>
      </div>
      <div class="col-12 col-lg-12">
        <button type="submit" class="btn btn-success btn-block">Editar empresa</button>
      </div>
    </form>

  </div>
</div>
