
<div class="card">
  <div class="card-body">
    <form action="/particulares/{{ $particular->id }}" method="POST">
          @csrf
          {{ method_field("PATCH")}}


      <div class="form-group">
        <label>telefono</label>
        <input type="text" name="telefono" class="form-control" value="{{$particular->telefono}}"></input>
      </div>
      <div class="row">
        <div class="col-6">
          <div class="form-group">
            <label>calle</label>
            <input type="text" name="calle" class="form-control" value="{{$particular->calle}}"></input>
          </div>
        </div>
        <div class="col-6">
          <div class="form-group">
            <label>numero</label>
            <input type="text" name="numero" class="form-control" value="{{$particular->numero}}"></input>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-6">
          <div class="form-group">
            <label>piso</label>
            <input type="text" name="piso" class="form-control" value="{{$particular->piso}}"></input>
          </div>
        </div>
        <div class="col-6">
          <div class="form-group">
            <label>depto</label>
            <input type="text" name="depto" class="form-control" value="{{$particular->depto}}"></input>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-6">

          <div class="form-group">
            <label>localidad</label>
            <input type="text" name="localidad" class="form-control" value="{{$particular->localidad}}"></input>
          </div>
        </div>
        <div class="col-6">
          <div class="form-group">
            <label>provincia</label>
            <input type="text" name="provincia" class="form-control" value="{{$particular->provincia}}"></input>
          </div>

        </div>
      </div>
      <div class="form-group">
        <label>observaciones</label>
        <input type="text" name="observaciones" class="form-control" value="{{$particular->observaciones}}"></input>
      </div>
      <div class="col-12 col-lg-12">
        <button type="submit" class="btn btn-success btn-block">Editar particular</button>
      </div>
    </form>

  </div>
</div>
