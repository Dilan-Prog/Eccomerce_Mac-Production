<div class="tab-pane fade show active" id="list-home" role="tabpanel" aria-labelledby="list-home-list">
    <div class="card border">
        <div class="card-body">
            <form action="{{route('admin.paypal-setting.update', 1)}}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label>Paypal Estado</label>
                    <select class="form-control" name="status" id="">
                        <option {{ $paypalSetting->status == 1 ? 'selected' : '' }} value="1">Activado</option>
                        <option {{ $paypalSetting->status == 0 ? 'selected' : '' }} value="0">Desactivado</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Modo De Cuenta</label>
                    <br>
                    <small>(Modo sandbox es para pruebas de manera local)</small>
                    <select class="form-control" name="mode" id="">
                        <option {{ $paypalSetting->mode == 0 ? 'selected' : '' }} value="0">Sandbox</option>
                        <option {{ $paypalSetting->mode == 1 ? 'selected' : '' }} value="1">Live</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Nombre Del Pais</label>
                    <input type="text" class="form-control" name="country_name" value="{{ $paypalSetting->country_name }}">
                </div>
                <div class="form-group">
                    <label>Divisa</label>
                    <br>
                    <select class="form-control select2" name="currency_name" id="">
                        <option value="">Seleccionar</option>
                        @foreach (config('settings.currency_list') as $key => $currency)
                        <option {{ $key == $paypalSetting->currency_name ? 'selected' : '' }} value="{{ $key }}">{{ $currency }}</option>

                        @endforeach

                    </select>
                </div>
                <div class="form-group">
                    <label>Taza De Cambio (Por USD)</label>
                    <input type="text" class="form-control" name="currency_rate" value="{{ $paypalSetting->currency_rate }}">
                </div>
                <div class="form-group">
                    <label>Cliente De Paypal</label>
                    <input type="text" class="form-control" name="client_id" value="{{ $paypalSetting->client_id }}">
                </div>
                <div class="form-group">
                    <label>Paypal key</label>
                    <input type="text" class="form-control" name="secret_key" value="{{ $paypalSetting->secret_key }}">
                </div>

                <button type="submit" class="btn btn-primary" >Guardar</button>
            </form>
        </div>
    </div>
</div>
