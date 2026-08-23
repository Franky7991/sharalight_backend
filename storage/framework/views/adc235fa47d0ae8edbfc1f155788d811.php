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
                                    <?php
                                        $ingNames = $a->details
                                            ->filter(fn($d) => $d->product && $d->product->type === \App\Models\Product::TYPE_RAW_MATERIAL)
                                            ->map(fn($d) => $d->product->name)
                                            ->unique()
                                            ->toArray();
                                    ?>
                                    <?php if(!empty($ingNames)): ?>
                                        — <?php echo e(implode(', ', $ingNames)); ?>

                                    <?php endif; ?>
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
                            <th>Ingredienti</th>
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
<?php if(!$productionOrder->isCreated()): ?>
<div class="row mt-2">
    <div class="col-12">
        <div class="card">
            <div class="card-header pb-0">
                <div class="d-flex align-items-center justify-content-between">
                    <h4 class="mb-0"><i class="fas fa-industry mr-1"></i> Produzione</h4>
                </div>
            </div>
            <div class="card-body">

                <?php if($productionOrder->isCompleted()): ?>
                    <div class="alert alert-success mb-3">
                        <i class="fas fa-check-circle mr-1"></i> Produzione completata.
                    </div>
                <?php endif; ?>

                <h6 class="text-uppercase text-muted font-weight-bold mb-2" style="font-size:.75rem; letter-spacing:.05em;">
                    <i class="fas fa-box mr-1"></i> Prodotti da produrre
                </h6>

                <?php if(count($plan['products'])): ?>
                    <table class="table table-sm table-bordered mb-4">
                        <thead class="thead-light">
                            <tr>
                                <th>Prodotto</th>
                                <th>Ingredienti</th>
                                <th class="text-right">Ordinata</th>
                                <th class="text-right">Prodotta</th>
                                <th>U.M.</th>
                                <th style="width:120px;">Azione</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $plan['products']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($p['product_name']); ?></td>
                                    <td class="small">
                                        <?php if(count($p['ingredients'])): ?>
                                            <ul class="list-unstyled mb-0">
                                                <?php $__currentLoopData = $p['ingredients']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <li><?php echo e($ing['product_name']); ?> — <?php echo e(number_format($ing['qnt'], 2, ',', '.')); ?> <?php echo e($ing['uom_symbol']); ?></li>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </ul>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-right"><?php echo e(number_format($p['qnt'], 2, ',', '.')); ?></td>
                                    <td class="text-right font-weight-bold <?php echo e($p['remaining'] > 0 ? 'text-warning' : 'text-success'); ?>">
                                        <?php echo e(number_format($p['qnt_produced'], 2, ',', '.')); ?>

                                    </td>
                                    <td><?php echo e($p['uom_symbol']); ?></td>
                                    <td>
                                        <?php if($productionOrder->isInProcessing() && $p['remaining'] > 0): ?>
                                            <button type="button" class="btn btn-success btn-sm btn-produce-product"
                                                    data-detail-id="<?php echo e($p['id']); ?>"
                                                    data-product="<?php echo e($p['product_name']); ?>"
                                                    data-uom="<?php echo e($p['uom_symbol']); ?>"
                                                    data-remaining="<?php echo e($p['remaining']); ?>">
                                                <i class="fa fa-play mr-1"></i> Produci
                                            </button>
                                        <?php elseif($p['remaining'] <= 0): ?>
                                            <span class="badge badge-success">Completato</span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted small">Nessun prodotto da produrre.</p>
                <?php endif; ?>

                <h6 class="text-uppercase text-muted font-weight-bold mb-2" style="font-size:.75rem; letter-spacing:.05em;">
                    <i class="fas fa-cubes mr-1"></i> Materie prime necessarie
                </h6>

                <?php if($plan['missing_count'] > 0): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        <strong><?php echo e($plan['missing_count']); ?></strong> materiale/i mancante/i a magazzino.
                    </div>
                <?php endif; ?>

                <?php if(count($plan['materials'])): ?>
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Materiale</th>
                                <th class="text-right">Richiesta</th>
                                <th class="text-right">Conversione</th>
                                <th class="text-right">Giacenza</th>
                                <th class="text-right">Mancante</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $plan['materials']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="<?php echo e($m['is_missing'] ? 'table-danger' : ''); ?>">
                                    <td><?php echo e($m['product_name']); ?></td>
                                    <td class="text-right"><?php echo e(number_format($m['required_qnt'], 2, ',', '.')); ?> <?php echo e($m['uom_symbol']); ?></td>
                                    <td class="text-right">
                                        <?php if($m['has_conversion']): ?>
                                            <?php echo e(number_format($m['original_qnt'], 2, ',', '.')); ?> <?php echo e($m['original_uom_symbol']); ?> → <?php echo e(number_format($m['required_qnt'], 2, ',', '.')); ?> <?php echo e($m['uom_symbol']); ?>

                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-right"><?php echo e(number_format($m['available_qnt'], 2, ',', '.')); ?> <?php echo e($m['uom_symbol']); ?></td>
                                    <td class="text-right <?php echo e($m['is_missing'] ? 'text-danger font-weight-bold' : 'text-muted'); ?>">
                                        <?php echo e(number_format($m['missing_qnt'], 2, ',', '.')); ?> <?php echo e($m['uom_symbol']); ?>

                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted small mb-0">Nessuna materia prima richiesta.</p>
                <?php endif; ?>

                <h6 class="text-uppercase text-muted font-weight-bold mb-2 mt-4" style="font-size:.75rem; letter-spacing:.05em;">
                    <i class="fas fa-history mr-1"></i> Produzioni registrate
                </h6>

                <?php if($productionOrder->records && $productionOrder->records->count()): ?>
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Data</th>
                                <th>Prodotto</th>
                                <th>Ingredienti utilizzati</th>
                                <th class="text-right">Quantità</th>
                                <th>U.M.</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $productionOrder->records; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($rec->created_at?->format('d/m/Y H:i')); ?></td>
                                    <td><?php echo e($rec->product?->name ?? '-'); ?></td>
                                    <td class="small">
                                        <?php
                                            $ingMovements = $rec->movements->filter(fn($m) => $m->product_id !== $rec->product_id);
                                        ?>
                                        <?php if($ingMovements->isNotEmpty()): ?>
                                            <ul class="list-unstyled mb-0">
                                                <?php $__currentLoopData = $ingMovements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $im): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <li><?php echo e($im->product?->name ?? '?'); ?> — <?php echo e(number_format(abs((float)$im->qnt), 2, ',', '.')); ?> <?php echo e($im->unitOfMeasure?->symbol ?? ''); ?></li>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </ul>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-right"><?php echo e(number_format((float)$rec->qnt, 2, ',', '.')); ?></td>
                                    <td><?php echo e($rec->unitOfMeasure?->symbol ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted small mb-0">Nessuna produzione registrata.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>


<div class="modal fade" id="modal-produce" tabindex="-1" role="dialog" aria-labelledby="modal-produce-label" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-produce-label"><i class="fa fa-play mr-1"></i> Produzione</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Chiudi">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="produce-detail-id" value="">
                <input type="hidden" id="produce-uom" value="">

                <div class="form-group">
                    <label for="produce-qnt">Quantità da produrre ora <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-box"></i></span>
                        </div>
                        <input type="number" id="produce-qnt" class="form-control" step="0.01" min="0.01">
                        <div class="input-group-append">
                            <span class="input-group-text" id="produce-info"></span>
                        </div>
                    </div>
                </div>

                <div id="produce-status" class="small mb-2"></div>

                <table class="table table-sm table-bordered mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Materiale</th>
                            <th class="text-right">Richiesta</th>
                            <th class="text-right">Conversione</th>
                            <th class="text-right">Giacenza</th>
                            <th class="text-right">Mancante</th>
                        </tr>
                    </thead>
                    <tbody id="produce-materials-table"></tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fa fa-times"></i> Annulla
                </button>
                <button type="button" class="btn btn-success btn-sm" id="btn-confirm-produce" disabled>
                    <i class="fa fa-play mr-1"></i> Produci
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
            { data: 'ingredients_html',           name: 'ingredients_html', orderable: false },
            { data: 'customer_order_progressive', name: 'customer_order_progressive' },
            { data: 'qnt',                        name: 'qnt' },
            { data: 'uom_symbol',                 name: 'uom_symbol' },
            { data: 'id',                         name: 'id', orderable: false, searchable: false },
        ],
        columnDefs: [
            {
                targets: 5,
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

    var produceTimer = null;
    var produceDetailId = null;
    var produceProductName = null;

    function formatIt(val) {
        var n = parseFloat(val);
        if (isNaN(n)) return '-';
        return n.toLocaleString('it-IT', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function refreshProduceRequirements() {
        var qnt = $('#produce-qnt').val();
        var $status = $('#produce-status');
        var $btn = $('#btn-confirm-produce');
        var $tbody = $('#produce-materials-table');

        if (!produceDetailId || !qnt || parseFloat(qnt.replace(',', '.')) <= 0) {
            $tbody.html('<tr><td colspan="5" class="text-center text-muted">Indicare una quantità da produrre.</td></tr>');
            $status.html('');
            $btn.prop('disabled', true);
            return;
        }

        $.ajax({
            url: '/production-orders/' + productionOrderId + '/products/' + produceDetailId + '/requirements',
            type: 'GET',
            data: { qnt: qnt },
            success: function (data) {
                if (data.sufficient) {
                    $status.html('<span class="text-success"><i class="fa fa-check-circle mr-1"></i> Giacenza sufficiente</span>');
                    $btn.prop('disabled', false);
                } else {
                    $status.html('<span class="text-danger"><i class="fa fa-exclamation-triangle mr-1"></i> Materie prime mancanti a magazzino</span>');
                    $btn.prop('disabled', true);
                }

                var rows = '';
                if (data.materials && data.materials.length) {
                    data.materials.forEach(function (m) {
                        var cls = m.is_missing ? 'table-danger' : '';
                        var missingCls = m.is_missing ? 'text-danger font-weight-bold' : 'text-muted';
                        var convCell = m.has_conversion
                            ? formatIt(m.original_qnt) + ' ' + m.original_uom_symbol + ' → ' + formatIt(m.required_qnt) + ' ' + m.uom_symbol
                            : '<span class="text-muted">—</span>';
                        rows += '<tr class="' + cls + '">'
                              + '<td>' + $('<span>').text(m.product_name).html() + '</td>'
                              + '<td class="text-right">' + formatIt(m.required_qnt) + ' ' + m.uom_symbol + '</td>'
                              + '<td class="text-right">' + convCell + '</td>'
                              + '<td class="text-right">' + formatIt(m.available_qnt) + ' ' + m.uom_symbol + '</td>'
                              + '<td class="text-right ' + missingCls + '">' + formatIt(m.missing_qnt) + ' ' + m.uom_symbol + '</td>'
                              + '</tr>';
                    });
                } else {
                    rows = '<tr><td colspan="5" class="text-center text-muted">Nessuna materia prima richiesta.</td></tr>';
                }
                $tbody.html(rows);
            },
            error: function () {
                $status.html('<span class="text-danger">Errore nel caricamento della giacenza.</span>');
                $btn.prop('disabled', true);
                $tbody.html('<tr><td colspan="5" class="text-center text-muted">Errore nel caricamento.</td></tr>');
            },
        });
    }

    $(document).on('click', '.btn-produce-product', function () {
        var btn = $(this);
        produceDetailId = btn.data('detail-id');
        produceProductName = btn.data('product');

        $('#produce-detail-id').val(produceDetailId);
        $('#produce-uom').val(btn.data('uom'));
        $('#modal-produce-label').text('Produzione: ' + produceProductName);
        $('#produce-info').text('(max ' + formatIt(btn.data('remaining')).replace(/\s/g, '') + ' ' + btn.data('uom') + ')');
        $('#produce-qnt').attr('max', btn.data('remaining'));
        $('#produce-qnt').val(btn.data('remaining'));
        $('#produce-status').html('');
        $('#produce-materials-table').html('<tr><td colspan="5" class="text-center text-muted">Caricamento giacenza…</td></tr>');
        $('#modal-produce').modal('show');

        refreshProduceRequirements();
    });

    $(document).on('input', '#produce-qnt', function () {
        clearTimeout(produceTimer);
        produceTimer = setTimeout(refreshProduceRequirements, 250);
    });

    $('#btn-confirm-produce').on('click', function () {
        var qnt = $('#produce-qnt').val();
        if (!qnt || parseFloat(qnt.replace(',', '.')) <= 0) {
            alert('Indicare una quantità da produrre.');
            return;
        }

        if (!confirm('Produrre ' + qnt + ' ' + $('#produce-uom').val() + ' di "' + produceProductName + '"?')) return;

        $.ajax({
            url: '<?php echo e(route('production-orders.produce', $productionOrder->id)); ?>',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: {
                production_order_detail_id: produceDetailId,
                qnt: qnt,
            },
            success: function () {
                window.location.reload();
            },
            error: function (xhr) {
                var m = 'Errore durante la produzione.';
                if (xhr.responseJSON && xhr.responseJSON.message) m = xhr.responseJSON.message;
                alert(m);
            },
        });
    });

});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('adminlte::page', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\project\shara_light\backend\resources\views/production_order/show.blade.php ENDPATH**/ ?>