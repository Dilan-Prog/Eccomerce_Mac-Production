@extends('admin-ui.layouts.master')

@section('title', 'Control De Usuarios')

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => 'Control De Usuarios',
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Control De Usuarios'],
        ],
    ])

    <div class="au-card">
        <div class="au-card-header">
            <div class="au-card-title">Crear Usuario</div>
        </div>
        <div class="au-card-body">
            <form action="{{ route('admin.manage-user.create') }}" method="POST">
                @csrf
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px">
                    <div class="au-field">
                        <label class="au-label">Nombre</label>
                        <input type="text" placeholder="Juan Perez" class="au-input" name="name" value="">
                    </div>
                    <div class="au-field">
                        <label class="au-label">Apellido</label>
                        <input type="text" placeholder="Benitez Aragon" class="au-input" name="last_name" value="">
                    </div>
                    <div class="au-field">
                        <label class="au-label">Empresa</label>
                        <input type="text" placeholder="Honeywell" class="au-input" name="company" value="">
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px">
                    <div class="au-field">
                        <label class="au-label">RFC</label>
                        <input type="text" placeholder="RFC123456789" class="au-input" name="rfc" value="">
                    </div>
                    <div class="au-field">
                        <label class="au-label">Telefono personal</label>
                        <input type="text" placeholder="555-123-4567" class="au-input" name="phone" value="">
                    </div>
                    <div class="au-field">
                        <label class="au-label" for="inputState">Rol</label>
                        <select id="inputState" class="au-select" name="role">
                            <option value="">Seleccionar</option>
                            <option value="user">Usuario</option>
                            <option value="associate">Asociado</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                    <div class="au-field">
                        {{-- Requiere migracion de una nueva tabla dentro de productos --}}
                        <label class="au-label" for="inputTypePrice">Precio Especial</label>
                        <select id="inputTypePrice" class="au-select" name="type_price" disabled>
                            <option value="">Seleccionar</option>
                            <option value="1">Precio Publico</option>
                            <option value="2">Precio Minimo</option>
                            <option value="3">Precio Liquidacion</option>
                        </select>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px">
                    <div class="au-field">
                        <label class="au-label">Correo Electrónico</label>
                        <input type="text" placeholder="correo@ejemplo.com" class="au-input" name="email" value="">
                    </div>
                    <div class="au-field">
                        <label class="au-label">Contraseña</label>
                        <input type="password" placeholder="**************" class="au-input" name="password" value="">
                    </div>
                    <div class="au-field">
                        <label class="au-label">Confirmar Contraseña</label>
                        <input type="password" placeholder="**************" class="au-input" name="password_confirmation" value="">
                    </div>
                </div>
                <button type="submit" class="au-btn au-btn-primary">Crear</button>
            </form>
        </div>
    </div>
@endsection
