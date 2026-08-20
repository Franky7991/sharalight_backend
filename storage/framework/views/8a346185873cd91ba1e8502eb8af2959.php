<?php $__env->startSection('title', 'Ordini di Produzione'); ?>
<?php $__env->startSection('content_header'); ?><?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-header pb-0">
        <div class="d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Ordini di Produzione</h4>
            <div>
                <button type="button" class="btn btn-danger btn-sm mr-1" id="btn-bulk-delete">
                    <i class="fa fa-trash"></i> Cancella
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="btn-new-production-order">
                    <i class="fa fa-plus"></i> Nuovo
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <table id="table_production_orders" class="table table-hover" width="100%">
            <thead>
                <tr>
                    <th><input class="form-check-input" type="checkbox"></th>
                    <th>Progressivo</th>
                    <th>Data Produzione</th>
                    <th>Magazzino</th>
                    <th>Stato</th>
                    <th style="width:80px;">Azioni</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modal-production-order" tabindex="-1" role="dialog"
     aria-labelledby="modal-production-order-label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-production-order-label">Nuovo Ordine di Produzione</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Chiudi">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <div id="po-errors" class="alert alert-danger d-none">
                    <ul class="mb-0" id="po-errors-list"></ul>
                </div>

                <input type="hidden" id="po_id" value="">

                <div class="form-group">
                    <label for="po_production_date">Data Produzione <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                        </div>
                        <input type="date" id="po_production_date" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label for="po_warehouse_id">Magazzino <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-warehouse"></i></span>
                        </div>
                        <select id="po_warehouse_id" class="form-control">
                            <option value="">-- Seleziona --</option>
                            <?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($w->id); ?>"><?php echo e($w->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fa fa-times"></i> Annulla
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="btn-save-production-order">
                    <i class="fa fa-save"></i> Salva
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

    var ordersTable = $('#table_production_orders').DataTable({
        order: [[1, 'desc']],
        pageLength: 25,
        ajax: {
            type: 'POST',
            url: '<?php echo e(route('production-orders.datatable')); ?>',
            headers: { 'X-CSRF-TOKEN': csrfToken },
        },
        columns: [
            { searchable: false, orderable: false, data: null, defaultContent: '', class: 'disableEdit' },
            { data: 'progressive',         name: 'progressive' },
            { data: 'production_date_fmt', name: 'production_date' },
            { data: 'warehouse_name',      name: 'warehouse_name', orderable: false },
            { data: 'state_label',         name: 'state', orderable: false },
            { data: 'id',                  name: 'id', orderable: false, searchable: false },
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
                    var html = '<a href="/production-orders/' + id + '" class="btn btn-info btn-xs mr-1" title="Apri">'
                             + '<i class="fa fa-eye"></i></a>';

                    if (row.state === 'created') {
                        html += '<button class="btn btn-primary btn-xs btn-edit-production-order mr-1"'
                              + ' data-id="' + id + '"'
                              + ' data-production-date="' + row.production_date_ymd + '"'
                              + ' data-warehouse-id="' + row.warehouse_id + '"'
                              + ' title="Modifica"><i class="fa fa-edit"></i></button>';
                    }

                    html += '<button class="btn btn-danger btn-xs btn-delete-production-order"'
                          + ' data-id="' + id + '" title="Elimina"><i class="fa fa-trash"></i></button>';

                    return html;
                }
            },
        ],
    });

    $('#btn-new-production-order').on('click', function () {
        resetModal();
        $('#modal-production-order-label').text('Nuovo Ordine di Produzione');
        $('#modal-production-order').modal('show');
    });

    $('#table_production_orders').on('click', '.btn-edit-production-order', function () {
        var btn = $(this);
        resetModal();
        $('#modal-production-order-label').text('Modifica Ordine di Produzione');
        $('#po_id').val(btn.data('id'));
        $('#po_production_date').val(btn.data('production-date'));
        $('#po_warehouse_id').val(btn.data('warehouse-id'));
        $('#modal-production-order').modal('show');
    });

    $('#btn-save-production-order').on('click', function () {
        hideErrors();
        var id = $('#po_id').val();
        $.ajax({
            url: id ? '/production-orders/' + id : '<?php echo e(route('production-orders.store')); ?>',
            type: id ? 'PUT' : 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: {
                production_date: $('#po_production_date').val(),
                warehouse_id:    $('#po_warehouse_id').val(),
            },
            success: function () {
                $('#modal-production-order').modal('hide');
                ordersTable.ajax.reload(null, false);
            },
            error: function (xhr) {
                if (xhr.status === 422) showErrors(xhr.responseJSON.errors);
                else if (xhr.responseJSON && xhr.responseJSON.message) alert(xhr.responseJSON.message);
                else alert('Errore durante il salvataggio.');
            },
        });
    });

    $('#table_production_orders').on('click', '.btn-delete-production-order', function () {
        if (!confirm('Eliminare questo ordine di produzione?')) return;
        $.ajax({
            url: '/production-orders/' + $(this).data('id'),
            type: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function () { ordersTable.ajax.reload(null, false); },
            error: function (xhr) {
                var msg = 'Errore durante l\'eliminazione.';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                alert(msg);
            },
        });
    });

    $('#btn-bulk-delete').on('click', function () {
        var ids = [];
        $('#table_production_orders tbody input[name="selected[]"]:checked').each(function () {
            ids.push($(this).val());
        });

        if (!ids.length) {
            alert('Selezionare almeno un ordine di produzione.');
            return;
        }

        if (!confirm('Eliminare gli ordini selezionati?')) return;

        $.ajax({
            url: '<?php echo e(route('production-orders.delete')); ?>',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: { ids: ids },
            success: function () { ordersTable.ajax.reload(null, false); },
            error: function () { alert('Errore durante l\'eliminazione.'); },
        });
    });

    function resetModal() {
        $('#po_id').val('');
        $('#po_production_date').val('');
        $('#po_warehouse_id').val('');
        hideErrors();
    }

    function showErrors(errors) {
        var list = $('#po-errors-list').empty();
        $.each(errors, function (f, msgs) {
            $.each(msgs, function (i, msg) { list.append('<li>' + msg + '</li>'); });
        });
        $('#po-errors').removeClass('d-none');
    }

    function hideErrors() {
        $('#po-errors').addClass('d-none');
        $('#po-errors-list').empty();
    }

});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('adminlte::page', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\project\shara_light\backend\resources\views/production_order/index.blade.php ENDPATH**/ ?>