@extends('admin.layouts.master')

@section('content')
      <!-- Main Content -->
        <section class="section">
          <div class="section-header">
            <h1>Customer list</h1>
          </div>

          <div class="section-body">

            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-header">
                    <h4>All customer</h4>
                  </div>
                  <div class="card-body">
                    {{ $dataTable->table() }}
                  </div>
                </div>
              </div>
            </div>

          </div>
        </section>

        <!-- Modal previsualización CSF -->
        <div class="modal fade" id="csf-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-file-pdf text-danger mr-2"></i>
                            Constancia de Situación Fiscal
                        </h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body p-0" style="height:80vh;">
                        <iframe id="csf-iframe" src="" style="width:100%;height:100%;border:none;" title="CSF"></iframe>
                    </div>
                    <div class="modal-footer">
                        <a id="csf-download-link" href="#" target="_blank" class="btn btn-primary btn-sm">
                            <i class="fas fa-download mr-1"></i> Abrir en nueva pestaña
                        </a>
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal subir / reemplazar CSF (admin) -->
        <div class="modal fade" id="csf-upload-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-upload mr-2"></i>
                            Subir CSF del cliente
                        </h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <form id="csf-upload-form" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <p class="text-muted mb-3" id="csf-upload-customer-name"></p>
                            <div class="form-group">
                                <label class="font-weight-bold">Archivo PDF <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="csf-file-input" name="csf_file" accept="application/pdf,.pdf" required>
                                        <label class="custom-file-label" for="csf-file-input">Selecciona un PDF...</label>
                                    </div>
                                </div>
                                <small class="text-muted">Solo PDF · Máx. 5 MB</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="csf-upload-submit">
                                <i class="fas fa-upload mr-1"></i> Subir CSF
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

@endsection

@push('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}

    <script>
        var CSF_BASE_URL = "{{ url('admin/customer') }}";
        var CSRF_TOKEN   = "{{ csrf_token() }}";

        $(document).ready(function(){

            // Cambio de estado activo/inactivo
            $('body').on('click', '.change-status', function(){
                let isChecked = $(this).is(':checked');
                let id = $(this).data('id');

                $.ajax({
                    url: "{{route('admin.customer.status-change')}}",
                    method: 'PUT',
                    data: { status: isChecked, id: id },
                    success: function(data){ toastr.success(data.message) },
                    error: function(xhr, status, error){ console.log(error); }
                });
            });

            // Vista previa CSF
            $('body').on('click', '.btn-csf-preview', function(e){
                e.preventDefault();
                var url = $(this).data('url');
                $('#csf-iframe').attr('src', url);
                $('#csf-download-link').attr('href', url);
                $('#csf-modal').modal('show');
            });

            $('#csf-modal').on('hidden.bs.modal', function(){
                $('#csf-iframe').attr('src', '');
            });

            // Abrir modal de upload
            $(document).on('click', '.btn-csf-upload', function(){
                var id   = $(this).data('id');
                var name = $(this).data('name');
                $('#csf-upload-form').data('user-id', id);
                $('#csf-upload-customer-name').text('Cliente: ' + name);
                $('#csf-file-input').val('');
                $('#csf-file-input').next('.custom-file-label').text('Selecciona un PDF...');
                $('#csf-upload-modal').modal('show');
            });

            // Mostrar nombre del archivo seleccionado
            $(document).on('change', '#csf-file-input', function(){
                var fileName = this.files.length ? this.files[0].name : 'Selecciona un PDF...';
                $(this).next('.custom-file-label').text(fileName);
            });

            // Submit upload CSF
            $('#csf-upload-form').on('submit', function(e){
                e.preventDefault();
                var userId = $(this).data('user-id');
                var formData = new FormData(this);
                formData.set('_token', CSRF_TOKEN);
                var btn = $('#csf-upload-submit');

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Subiendo...');

                $.ajax({
                    url: CSF_BASE_URL + '/' + userId + '/csf',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
                    success: function(res){
                        toastr.success(res.message);
                        $('#csf-upload-modal').modal('hide');
                        $('#customerlist-table').DataTable().ajax.reload(null, false);
                    },
                    error: function(xhr){
                        var msg = xhr.responseJSON && xhr.responseJSON.message
                            ? xhr.responseJSON.message
                            : 'Error al subir el archivo. Código: ' + xhr.status;
                        toastr.error(msg);
                        console.error(xhr.responseText);
                    },
                    complete: function(){
                        btn.prop('disabled', false).html('<i class="fas fa-upload mr-1"></i> Subir CSF');
                    }
                });
            });

        });
    </script>
@endpush
