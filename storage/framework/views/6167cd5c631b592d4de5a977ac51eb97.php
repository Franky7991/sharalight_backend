
<?php $__env->startSection('title', 'Ordini Cliente'); ?>
<?php $__env->startSection('content_header'); ?><?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-header pb-0">
        <div class="d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Ordini Cliente</h4>
            <div>
                <button type="button" class="btn btn-danger btn-sm js-delete mr-1"
                        data-list="table_orders" data-url="<?php echo e(route('customer-orders.delete')); ?>">
                    <i class="fa fa-trash"></i> Cancella
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="btn-new-order">
                    <i class="fa fa-plus"></i> Nuovo
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <table id="table_orders" class="table table-hover" width="100%">
            <thead>
                <tr>
                    <th><input class="form-check-input" type="checkbox" onClick="toggle(this, 'selected[]')"></th>
                    <th>Progressivo</th>
                    <th>Data Ordine</th>
                    <th>Indirizzo</th>
                    <th>Utente</th>
                    <th>Stato</th>
                    <th style="width:80px;">Azioni</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>


<div class="modal fade" id="modal-order" tabindex="-1" role="dialog"
     aria-labelledby="modal-order-label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-order-label">Nuovo Ordine</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Chiudi">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <div id="modal-order-errors" class="alert alert-danger d-none">
                    <ul class="mb-0" id="modal-order-errors-list"></ul>
                </div>

                <input type="hidden" id="order_id" value="">

                
                <div class="form-group">
                    <label for="order_order_date">Data Ordine <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                        </div>
                        <input type="date" id="order_order_date" class="form-control">
                    </div>
                </div>

                
                <div class="form-group">
                    <label for="order_address">Indirizzo <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                        </div>
                        <textarea id="order_address" class="form-control" rows="2"
                                  placeholder="Via, Città, CAP…"></textarea>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fa fa-times"></i> Annulla
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="btn-save-order">
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

    // ---- DataTable ordini -----------------------------------------------
    var ordersTable = $('#table_orders').DataTable({
        order: [[1, 'desc']],
        pageLength: 25,
        ajax: {
            type: 'POST',
            url: '<?php echo e(route('customer-orders.datatable')); ?>',
            headers: { 'X-CSRF-TOKEN': csrfToken },
        },
        columns: [
            { searchable: false, orderable: false, data: null, defaultContent: '', class: 'disableEdit' },
            { data: 'progressive',     name: 'progressive' },
            { data: 'order_date_fmt',  name: 'order_date' },
            { data: 'address',         name: 'address' },
            { data: 'user_name',       name: 'user_name' },
            { data: 'state_label',     name: 'state', orderable: false },
            { data: 'id',              name: 'id', orderable: false, searchable: false },
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
                targets: 6,
                render: function (id, type, row) {
                    return '<a href="/customer-orders/' + id + '" class="btn btn-info btn-xs mr-1" title="Apri">'
                         + '<i class="fa fa-eye"></i></a>'
                         + '<button class="btn btn-primary btn-xs btn-edit-order mr-1"'
                         + ' data-id="' + id + '"'
                         + ' data-address="' + $('<span>').text(row.address).html() + '"'
                         + ' data-date="' + row.order_date + '"'
                         + ' title="Modifica"><i class="fa fa-edit"></i></button>'
                         + '<button class="btn btn-danger btn-xs btn-delete-order"'
                         + ' data-id="' + id + '" title="Elimina"><i class="fa fa-trash"></i></button>';
                }
            },
        ],
    });

    // ---- Apri modal NUOVO -----------------------------------------------
    $('#btn-new-order').on('click', function () {
        resetModal();
        $('#modal-order-label').text('Nuovo Ordine');
        // Precompila con domani (primo giorno selezionabile)
        var tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        $('#order_order_date')
            .attr('min', tomorrow.toISOString().substring(0, 10))
            .val(tomorrow.toISOString().substring(0, 10));
        $('#modal-order').modal('show');
    });

    // ---- Apri modal MODIFICA --------------------------------------------
    $('#table_orders').on('click', '.btn-edit-order', function () {
        var btn = $(this);
        resetModal();
        $('#modal-order-label').text('Modifica Ordine');
        $('#order_id').val(btn.data('id'));
        var tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        $('#order_order_date')
            .attr('min', tomorrow.toISOString().substring(0, 10))
            .val(btn.data('date'));
        $('#order_address').val(btn.data('address'));
        $('#modal-order').modal('show');
    });

    // ---- Elimina --------------------------------------------------------
    $('#table_orders').on('click', '.btn-delete-order', function () {
        if (!confirm('Eliminare questo ordine?')) return;
        $.ajax({
            url:     '/customer-orders/' + $(this).data('id'),
            type:    'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function () { ordersTable.ajax.reload(null, false); },
            error:   function (xhr) {
                var msg = 'Errore durante l\'eliminazione.';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                alert(msg);
            },
        });
    });

    // ---- Salva ----------------------------------------------------------
    $('#btn-save-order').on('click', function () {
        hideErrors();
        var id = $('#order_id').val();
        $.ajax({
            url:     id ? '/customer-orders/' + id : '<?php echo e(route('customer-orders.store')); ?>',
            type:    id ? 'PUT' : 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: {
                address:    $('#order_address').val(),
                order_date: $('#order_order_date').val(),
            },
            success: function () {
                $('#modal-order').modal('hide');
                ordersTable.ajax.reload(null, false);
            },
            error: function (xhr) {
                if (xhr.status === 422) showErrors(xhr.responseJSON.errors);
                else alert('Errore durante il salvataggio.');
            },
        });
    });

    // ---- Helpers --------------------------------------------------------
    function resetModal() {
        $('#order_id, #order_address').val('');
        $('#order_order_date').val('');
        hideErrors();
    }
    function showErrors(errors) {
        var list = $('#modal-order-errors-list').empty();
        $.each(errors, function (f, msgs) {
            $.each(msgs, function (i, msg) { list.append('<li>' + msg + '</li>'); });
        });
        $('#modal-order-errors').removeClass('d-none');
    }
    function hideErrors() {
        $('#modal-order-errors').addClass('d-none');
        $('#modal-order-errors-list').empty();
    }

});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\project\shara_light\backend\resources\views/customer_order/index.blade.php ENDPATH**/ ?>