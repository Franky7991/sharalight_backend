<?php $__env->startSection('title', 'Ordine di Produzione ' . $productionOrder->progressive); ?>
<?php $__env->startSection('content_header'); ?><?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">

    <div class="col-md-4">
        <div class="card">
            <div class="card-header pb-0">
                <h4 class="mb-0">Dati Ordine di Produzione</h4>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5 text-muted">Progressivo</dt>
                    <dd class="col-7"><?php echo e($productionOrder->progressive); ?></dd>

                    <dt class="col-5 text-muted">Data Produzione</dt>
                    <dd class="col-7"><?php echo e($productionOrder->production_date?->format('d/m/Y')); ?></dd>

                    <dt class="col-5 text-muted">Magazzino</dt>
                    <dd class="col-7"><?php echo e($productionOrder->warehouse?->name ?? '-'); ?></dd>

                    <dt class="col-5 text-muted">Stato</dt>
                    <dd class="col-7">
                        <span class="badge badge-secondary" id="order-state-label"><?php echo e($productionOrder->stateLabel()); ?></span>
                    </dd>
                </dl>
                <div class="mt-3">
                    <?php if($productionOrder->isCreated()): ?>
                        <button type="button" class="btn btn-warning btn-sm btn-block" id="btn-in-processing">
                            <i class="fa fa-play mr-1"></i> In Lavorazione
                        </button>
                    <?php endif; ?>
                    <a href="<?php echo e(route('production-orders.index')); ?>" class="btn btn-secondary btn-sm btn-block mt-1">
                        <i class="fa fa-backward mr-1"></i> Indietro
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header pb-0">
                <h4 class="mb-0">Righe Ordine di Produzione</h4>
            </div>
            <div class="card-body">

                <?php if($productionOrder->isCreated()): ?>
                <div class="row mb-3">
                    <div class="col-9">
                        <select id="detail-select" class="form-control">
                            <option value="">-- Seleziona una riga ordine cliente --</option>
                            <?php $__currentLoopData = $available; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($a->id); ?>">
                                    <?php echo e($a->customerOrder?->progressive ?? '-'); ?> — <?php echo e($a->product?->name ?? '-'); ?>

                                    (<?php echo e(number_format((float)$a->qnt, 2, ',', '.')); ?> <?php echo e($a->unitOfMeasure?->symbol ?? ''); ?>)
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-3">
                        <button type="button" class="btn btn-primary btn-block btn-sm" id="btn-add-detail">
                            <i class="fa fa-plus"></i> Aggiungi
                        </button>
                    </div>
                </div>
                <?php endif; ?>

                <?php if($productionOrder->isCreated() && $available->isEmpty()): ?>
                    <div class="alert alert-info small mb-3">
                        <i class="fas fa-info-circle mr-1"></i>
                        Nessuna riga disponibile: servono ordini cliente nello stato "Prodotti Allocati".
                    </div>
                <?php endif; ?>

                <table id="table_details" class="table table-hover" width="100%">
                    <thead>
                        <tr>
                            <th>Prodotto</th>
                            <th>Ordine Cliente</th>
                            <th>Quantità</th>
                            <th>U.M.</th>
                            <th style="width:60px;">Azioni</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('js'); ?>
<script>
$(document).ready(function () {

    var csrfToken = $('meta[name="csrf-token"]').attr('content');
    var productionOrderId = "<?php echo e($productionOrder->id); ?>";
    var canModify = <?php echo e($productionOrder->isCreated() ? 'true' : 'false'); ?>;

    var detailsTable = $('#table_details').DataTable({
        order: [[0, 'asc']],
        pageLength: -1,
        ajax: {
            type: 'POST',
            url: '<?php echo e(route('production-order-details.datatable', $productionOrder->id)); ?>',
            headers: { 'X-CSRF-TOKEN': csrfToken },
        },
        columns: [
            { data: 'product_name',               name: 'product_name' },
            { data: 'customer_order_progressive', name: 'customer_order_progressive' },
            { data: 'qnt',                        name: 'qnt' },
            { data: 'uom_symbol',                 name: 'uom_symbol' },
            { data: 'id',                         name: 'id', orderable: false, searchable: false },
        ],
        columnDefs: [
            {
                targets: 4,
                render: function (data) {
                    if (!canModify) return '';
                    return '<button class="btn btn-danger btn-xs btn-remove-detail"'
                         + ' data-id="' + data + '" title="Rimuovi"><i class="fa fa-trash"></i></button>';
                }
            },
        ],
    });

    $('#btn-in-processing').on('click', function () {
        if (!confirm('Mettere l\'ordine di produzione in lavorazione? Questa operazione è irreversibile.')) return;
        $.ajax({
            url: '<?php echo e(route('production-orders.change-state', $productionOrder->id)); ?>',
            type: 'PUT',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: { state: '<?php echo e(\App\Models\ProductionOrder::STATE_IN_PROCESSING); ?>' },
            success: function () {
                window.location.reload();
            },
            error: function (xhr) {
                var msg = 'Errore durante il cambio di stato.';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                alert(msg);
            },
        });
    });

    $('#btn-add-detail').on('click', function () {
        var copId = $('#detail-select').val();
        if (!copId) {
            alert('Selezionare una riga ordine cliente.');
            return;
        }

        $.ajax({
            url: '<?php echo e(route('production-order-details.store', $productionOrder->id)); ?>',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: { customer_order_has_product_id: copId },
            success: function () {
                window.location.reload();
            },
            error: function (xhr) {
                var msg = 'Errore durante l\'aggiunta.';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                alert(msg);
            },
        });
    });

    $('#table_details').on('click', '.btn-remove-detail', function () {
        if (!confirm('Rimuovere questa riga?')) return;
        var detailId = $(this).data('id');

        $.ajax({
            url: '/production-orders/' + productionOrderId + '/details/' + detailId,
            type: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function () {
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
<?php echo $__env->make('adminlte::page', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\project\shara_light\backend\resources\views/production_order/show.blade.php ENDPATH**/ ?>