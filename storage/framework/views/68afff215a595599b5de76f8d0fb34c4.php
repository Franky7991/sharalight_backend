<?php $__env->startSection('title', 'Spedizioni'); ?>
<?php $__env->startSection('content_header'); ?><?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-header pb-0">
        <div class="d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Spedizioni</h4>
            <div>
                <button type="button" class="btn btn-danger btn-sm mr-1" id="btn-bulk-delete">
                    <i class="fa fa-trash"></i> Cancella
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="btn-new-shipment">
                    <i class="fa fa-plus"></i> Nuovo
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <table id="table_shipments" class="table table-hover" width="100%">
            <thead>
                <tr>
                    <th><input class="form-check-input" type="checkbox"></th>
                    <th>Progressivo</th>
                    <th>Data Spedizione</th>
                    <th>N. Ordini</th>
                    <th>Stato</th>
                    <th style="width:80px;">Azioni</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>


<div class="modal fade" id="modal-shipment" tabindex="-1" role="dialog"
     aria-labelledby="modal-shipment-label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-shipment-label">Nuova Spedizione</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Chiudi">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <div id="shp-errors" class="alert alert-danger d-none">
                    <ul class="mb-0" id="shp-errors-list"></ul>
                </div>

                <input type="hidden" id="shp_id" value="">

                <div class="form-group">
                    <label for="shp_date">Data Spedizione <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                        </div>
                        <input type="date" id="shp_date" class="form-control">
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
                <button type="button" class="btn btn-primary" id="btn-save-shipment">
                    <i class="fa fa-save mr-1"></i> Salva
                </button>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
<script>
$(document).ready(function () {

    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    var shipmentsTable = $('#table_shipments').DataTable({
        order: [[2, 'desc']],
        pageLength: 25,
        ajax: {
            type: 'POST',
            url: '<?php echo e(route('shipments.datatable')); ?>',
            headers: { 'X-CSRF-TOKEN': csrfToken },
        },
        columns: [
            { searchable: false, orderable: false, data: null, defaultContent: '', class: 'disableEdit' },
            { data: 'progressive',   name: 'progressive' },
            { data: 'date_fmt',      name: 'date' },
            { data: 'orders_count',  name: 'orders_count', orderable: false },
            { data: 'state_label',   name: 'state', orderable: false },
            { data: 'id',            name: 'id', orderable: false, searchable: false },
        ],
        columnDefs: [
            {
                targets: 0,
                render: function (data, type, row) {
                    return '<div class="form-check"><input class="form-check-input" type="checkbox"'
                         + ' name="selected[]" value="' + row.id + '"></div>';
                }
            },
            {
                targets: 5,
                render: function (id, type, row) {
                    return '<a href="/shipments/' + id + '" class="btn btn-info btn-xs mr-1" title="Apri">'
                             + '<i class="fa fa-eye"></i></a>'
                             + '<button class="btn btn-primary btn-xs btn-edit-shipment mr-1"'
                             + ' data-id="' + id + '"'
                             + ' data-date="' + row.date_ymd + '"'
                             + ' title="Modifica"><i class="fa fa-edit"></i></button>'
                             + '<button class="btn btn-danger btn-xs btn-delete-shipment"'
                             + ' data-id="' + id + '" title="Elimina"><i class="fa fa-trash"></i></button>';
                }
            },
        ],
    });

    $('#btn-new-shipment').on('click', function () {
        resetModal();
        $('#modal-shipment-label').text('Nuova Spedizione');
        $('#shp_date').val(new Date().toISOString().substring(0, 10));
        $('#modal-shipment').modal('show');
    });

    $('#table_shipments').on('click', '.btn-edit-shipment', function () {
        var btn = $(this);
        resetModal();
        $('#modal-shipment-label').text('Modifica Spedizione');
        $('#shp_id').val(btn.data('id'));
        $('#shp_date').val(btn.data('date'));
        $('#modal-shipment').modal('show');
    });

    $('#table_shipments').on('click', '.btn-delete-shipment', function () {
        if (!confirm('Eliminare questa spedizione?')) return;
        $.ajax({
            url: '/shipments/' + $(this).data('id'),
            type: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function () { shipmentsTable.ajax.reload(null, false); },
            error: function (xhr) {
                var msg = 'Errore durante l\'eliminazione.';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                alert(msg);
            },
        });
    });

    $('#btn-bulk-delete').on('click', function () {
        var ids = [];
        $('#table_shipments tbody input[name="selected[]"]:checked').each(function () {
            ids.push($(this).val());
        });

        if (!ids.length) {
            alert('Selezionare almeno una spedizione.');
            return;
        }

        if (!confirm('Eliminare le spedizioni selezionate?')) return;

        $.ajax({
            url: '<?php echo e(route('shipments.delete')); ?>',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: { ids: ids },
            success: function () { shipmentsTable.ajax.reload(null, false); },
            error: function () { alert('Errore durante l\'eliminazione.'); },
        });
    });

    $('#btn-save-shipment').on('click', function () {
        hideErrors();
        var id = $('#shp_id').val();
        $.ajax({
            url: id ? '/shipments/' + id : '<?php echo e(route('shipments.store')); ?>',
            type: id ? 'PUT' : 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: { date: $('#shp_date').val() },
            success: function () {
                $('#modal-shipment').modal('hide');
                shipmentsTable.ajax.reload(null, false);
            },
            error: function (xhr) {
                if (xhr.status === 422) showErrors(xhr.responseJSON.errors);
                else alert('Errore durante il salvataggio.');
            },
        });
    });

    function resetModal() {
        $('#shp_id').val('');
        $('#shp_date').val('');
        hideErrors();
    }
    function showErrors(errors) {
        var list = $('#shp-errors-list').empty();
        $.each(errors, function (f, msgs) {
            $.each(msgs, function (i, msg) { list.append('<li>' + msg + '</li>'); });
        });
        $('#shp-errors').removeClass('d-none');
    }
    function hideErrors() {
        $('#shp-errors').addClass('d-none');
        $('#shp-errors-list').empty();
    }

});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('adminlte::page', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\project\shara_light\backend\resources\views/shipment/index.blade.php ENDPATH**/ ?>