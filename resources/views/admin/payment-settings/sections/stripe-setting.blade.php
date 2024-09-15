<div class="tab-pane fade show" id="list-stripe" role="tabpanel" aria-labelledby="list-stripe-list">
    <div class="card border">
        <div class="card-body">
            <form action="{{route('admin.stripe-setting.update', 1)}}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label>Stripe Estado</label>
                    <select class="form-control" name="status" id="">
                        <option {{ $stripeSetting->status == 1 ? 'selected' : '' }} value="1">Activado</option>
                        <option {{ $stripeSetting->status == 0 ? 'selected' : '' }} value="0">Desactivado</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Modo De Cuenta</label>
                    <br>
                    <small>(Modo sandbox es para pruebas de manera local)</small>
                    <select class="form-control" name="mode" id="">
                        <option {{ $stripeSetting->mode == 0 ? 'selected' : '' }} value="0">Sandbox</option>
                        <option {{ $stripeSetting->mode == 1 ? 'selected' : '' }} value="1">Live</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Nombre Del Pais</label>
                    <input type="text" class="form-control" name="country_name" value="{{ $stripeSetting->country_name }}">
                </div>
                <div class="form-group">
                    <label>Divisa</label>
                    <br>
                    <select class="form-control select2" name="currency_name" id="">
                        <option value="">Seleccionar</option>
                        @foreach (config('settings.currency_list') as $key => $currency)
                        <option {{ $key == $stripeSetting->currency_name ? 'selected' : '' }} value="{{ $key }}">{{ $currency }}</option>

                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Taza De Cambio (Por USD)</label>
                    <input type="text" class="form-control" name="currency_rate" value="{{ $stripeSetting->currency_rate }}">
                </div>
                <div class="form-group">
                    <label>Cliente De Stripe</label>
                    <input type="text" class="form-control" name="client_id" value="{{ $stripeSetting->client_id }}">
                </div>
                <div class="form-group">
                    <label>Stripe key</label>
                    <input type="text" class="form-control" name="secret_key" value="{{ $stripeSetting->secret_key }}">
                </div>

                <button type="submit" class="btn btn-primary" >Guardar</button>
            </form>
        </div>
    </div>
</div>
