@extends('admin.layouts.master')

@section('content')
      <!-- Main Content -->
        <section class="section">
          <div class="section-header">
            <h1>Gestionar Usuarios</h1>
          </div>
          <div class="section-body">
            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-header">
                    <h4>Crear Usuario</h4>
                  </div>
                  <div class="card-body">
                    <form action="{{route('admin.manage-user.create')}}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Nombre</label>
                                    <input type="text" placeholder="Juan Perez" class="form-control" name="name" value="">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Apellido</label>
                                    <input type="text" placeholder="Benitez Aragon" class="form-control" name="last_name" value="">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Empresa</label>
                                    <input type="text" placeholder="Honeywell" class="form-control" name="company" value="">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>RFC</label>
                                    <input type="text" placeholder="RFC123456789" class="form-control" name="rfc" value="">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Telefono personal</label>
                                    <input type="text" placeholder="555-123-4567" class="form-control" name="phone" value="">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="inputState">Role</label>
                                    <select id="inputState" class="form-control" name="role">
                                        <option value="">Seleccionar</option>
                                        <option value="user">Usuario</option>
                                        <option value="associate">Asociado</option>
                                        <option value="admin">Administrador</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="inputState">Precio Especial</label>
                                    <select id="inputState" class="form-control" name="type_price">
                                        <option value="">Seleccionar</option>
                                        <option value="1">Precio Publico</option>
                                        <option value="2">Precio Minimo</option>
                                        <option value="3">Precio Liquidacion</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Correo Electrónico</label>
                                    <input type="text" placeholder="correo@ejemplo.com" class="form-control" name="email" value="">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Contraseña</label>
                                    <input type="password" placeholder="**************" class="form-control" name="password" value="">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Confirmar Contraseña</label>
                                    <input type="password" placeholder="**************" class="form-control" name="password_confirmation" value="">
                                </div>
                            </div>
                        </div>
                        <button type="submmit" class="btn btn-primary">Crear</button>
                    </form>
                  </div>

                </div>
              </div>
            </div>
          </div>
        </section>
@endsection
