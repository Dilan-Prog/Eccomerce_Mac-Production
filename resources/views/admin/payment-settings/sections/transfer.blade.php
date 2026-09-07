<div class="tab-pane fade show active" id="list-transfer" role="tabpanel" aria-labelledby="list-home-list">
    <div class="card border">
        <div class="card-body">
            <form action="{{route('admin.transfer.update', 1)}}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label>Transferencia Estado</label>
                    <select class="form-control" name="status" id="">
                        <option {{ $transferSetting->status == 1 ? 'selected' : '' }} value="1">Activado</option>
                        <option {{ $transferSetting->status == 0 ? 'selected' : '' }} value="0">Desactivado</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Nombre del Banco</label>
                    <input type="text" class="form-control" name="nameBank" value="{{ $transferSetting->nameBank }}">
                </div>
                <div class="form-group">
                    <label>Titular</label>
                    <input type="text" class="form-control" name="nameTitular" value="{{ $transferSetting->nameTitular }}">
                </div>
                <div class="form-group">
                    <label>Numero De Cuenta</label>
                    <input type="text" class="form-control" name="accountNumber" value="{{ $transferSetting->accountNumber }}">
                </div>
                <div class="form-group">
                    <label>Numero De Tarjeta</label>
                    <input type="text" class="form-control" name="accountTarjet" value="{{ $transferSetting->accountTarjet }}">
                </div>
                <div class="form-group">
                    <label>Numero de Cuenta Clabe</label>
                    <input type="text" class="form-control" name="accountClabe" value="{{ $transferSetting->accountClabe }}">
                </div>
                <div class="form-group">
                    <label>RFC</label>
                    <input type="text" class="form-control" name="rfc" value="{{ $transferSetting->rfc }}">
                    <small class="form-text text-muted">Opcional. Se muestra al cliente junto a los datos bancarios.</small>
                </div>
                <div class="form-group">
                    <label>Moneda</label>
                    <input type="text" class="form-control" name="currency" value="{{ $transferSetting->currency }}" placeholder="MXN">
                    <small class="form-text text-muted">Opcional. Ej. MXN para moneda nacional.</small>
                </div>
                <div class="form-group">
                    <label>Correo para recibir comprobantes</label>
                    <input type="email" class="form-control" name="receiptEmail" value="{{ $transferSetting->receiptEmail }}" placeholder="ventas@macdelnorte.com">
                    <small class="form-text text-muted">
                        Es el correo que se le pide al cliente en el checkout para que envie su comprobante de transferencia.
                        No es el mismo al que llega el aviso automatico del pedido.
                    </small>
                </div>

                <button type="submit" class="btn btn-primary" >Guardar</button>
            </form>
        </div>
    </div>
</div>
