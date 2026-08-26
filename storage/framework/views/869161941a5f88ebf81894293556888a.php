<?php $__env->startSection('title', 'Spedizione ' . $shipment->progressive); ?>
<?php $__env->startSection('content_header'); ?><?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">

    <div class="col-md-4">
        <div class="card">
            <div class="card-header pb-0">
                <h4 class="mb-0">Dati Spedizione</h4>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5 text-muted">Progressivo</dt>
                    <dd class="col-7"><?php echo e($shipment->progressive); ?></dd>

                    <dt class="col-5 text-muted">Data Spedizione</dt>
                    <dd class="col-7"><?php echo e($shipment->date?->format('d/m/Y')); ?></dd>

                    <dt class="col-5 text-muted">Stato</dt>
                    <dd class="col-7">
                        <span class="badge badge-secondary" id="shipment-state-label"><?php echo e($shipment->stateLabel()); ?></span>
                    </dd>
                </dl>
                <div class="mt-3">
                    <a href="<?php echo e(route('shipments.index')); ?>" class="btn btn-secondary btn-sm btn-block">
                        <i class="fa fa-backward mr-1"></i> Indietro
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header pb-0">
                <h4 class="mb-0">Ordini Clienti in Spedizione</h4>
            </div>
            <div class="card-body">

                <?php if($shipment->isCreated()): ?>
                <div class="row mb-3">
                    <div class="col-9">
                        <select id="order-select" class="form-control">
                            <option value="">-- Seleziona un ordine cliente --</option>
                            <?php $__currentLoopData = $availableOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($a->id); ?>">
                                    <?php $pct = (int) round($a->productionProgress()); ?>
                                    <?php echo e($a->progressive); ?> — <?php echo e($a->user?->name ?? '?'); ?>

                                    (<?php echo e(number_format((float)$a->qnt, 2, ',', '.')); ?> — <?php echo e($pct); ?>%)
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-3">
                        <button type="button" class="btn btn-success btn-block" id="btn-add-order">
                            <i class="fa fa-plus mr-1"></i> Aggiungi
                        </button>
                    </div>
                </div>
                <?php endif; ?>

                <table id="table_shipment_orders" class="table table-hover" width="100%">
                    <thead>
                        <tr>
                            <th>Progressivo</th>
                            <th>Data Ordine</th>
                            <th>Indirizzo</th>
                            <th>Produzione</th>
                            <th style="width:60px;">Azioni</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<div class="card mt-3">
    <div class="card-header pb-0">
        <h4 class="mb-0">Prodotti degli Ordini in Spedizione</h4>
    </div>
    <div class="card-body">
        <table id="table_shipment_products" class="table table-hover" width="100%">
            <thead>
                <tr>
                    <th>Ordine</th>
                    <th>Prodotto</th>
                    <th class="text-right">Quantità</th>
                    <th>U.M.</th>
                    <th class="text-right">Q.ta Prodotta</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
<script>
$(document).ready(function () {

    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    var ordersTable = $('#table_shipment_orders').DataTable({
        order: [[0, 'asc']],
        pageLength: 25,
        ajax: {
            type: 'POST',
            url: '<?php echo e(route('shipment-details.datatable', $shipment->id)); ?>',
            headers: { 'X-CSRF-TOKEN': csrfToken },
        },
        columns: [
            { data: 'progressive',     name: 'progressive' },
            { data: 'order_date_fmt',  name: 'order_date_fmt', orderable: false },
            { data: 'address',         name: 'address', orderable: false },
            { data: 'progress_pct',    name: 'progress_pct', orderable: false,
                render: function (data, type, row) {
                    return (type === 'display') ? row.progress_bar_html : data;
                }
            },
            { data: 'id',              name: 'id', orderable: false, searchable: false },
        ],
        columnDefs: [
            {
                targets: 4,
                render: function (id, type, row) {
                    return '<button class="btn btn-danger btn-xs btn-remove-order"'
                         + ' data-id="' + id + '" title="Rimuovi"><i class="fa fa-trash"></i></button>';
                }
            },
        ],
    });

    // ---- Tabella prodotti degli ordini in spedizione ----------------------
    var productsTable = $('#table_shipment_products').DataTable({
        order: [[0, 'asc']],
        pageLength: 25,
        ajax: {
            type: 'POST',
            url: '<?php echo e(route('shipment-products.datatable', $shipment->id)); ?>',
            headers: { 'X-CSRF-TOKEN': csrfToken },
        },
        columns: [
            { data: 'order_progressive',      name: 'order_progressive' },
            { data: 'product_name',           name: 'product_name' },
            { data: 'qnt_fmt',                name: 'qnt', className: 'text-right' },
            { data: 'unit_of_measure_symbol', name: 'unit_of_measure_symbol', orderable: false },
            { data: 'qnt_produced_fmt',       name: 'qnt_produced', className: 'text-right' },
        ],
    });

    // ---- Aggiungi ordine -------------------------------------------------
    $('#btn-add-order').on('click', function () {
        var orderId = $('#order-select').val();
        if (!orderId) {
            alert('Selezionare un ordine cliente.');
            return;
        }

        $.ajax({
            url: '<?php echo e(route('shipment-details.store', $shipment->id)); ?>',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: { customer_order_id: orderId },
            success: function () {
                $('#order-select').val('');
                ordersTable.ajax.reload(null, false);
                window.location.reload();
            },
            error: function (xhr) {
                var msg = 'Errore durante l\'aggiunta.';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                alert(msg);
            },
        });
    });

    // ---- Rimuovi ordine ----------------------------------------------------
    var destroyUrlBase = '<?php echo e(route('shipment-details.destroy', [$shipment->id, '__DETAIL__'])); ?>';
    $('#table_shipment_orders').on('click', '.btn-remove-order', function () {
        if (!confirm('Rimuovere questo ordine dalla spedizione?')) return;
        $.ajax({
            url: destroyUrlBase.replace('__DETAIL__', $(this).data('id')),
            type: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function () {
                ordersTable.ajax.reload(null, false);
                window.location.reload();
            },
            error: function (xhr) {
                var msg = 'Errore durante la rimozione.';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                alert(msg);
            },
        });
    });

});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('adminlte::page', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\project\shara_light\backend\resources\views\shipment\show.blade.php ENDPATH**/ ?>