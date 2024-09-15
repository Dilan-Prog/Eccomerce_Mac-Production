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
                    <label>Clabe Interbancaria</label>
                    <input type="text" class="form-control" name="accountClabe" value="{{ $transferSetting->accountClabe }}">
                </div>

                <button type="submit" class="btn btn-primary" >Guardar</button>
            </form>
        </div>
    </div>
</div>
