
<?php $__env->startSection('title', 'Ordine ' . $order->progressive); ?>
<?php $__env->startSection('content_header'); ?><?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">

    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header pb-0">
                <h4 class="mb-0">Dati Ordine</h4>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5 text-muted">Progressivo</dt>
                    <dd class="col-7"><?php echo e($order->progressive); ?></dd>

                    <dt class="col-5 text-muted">Data Ordine</dt>
                    <dd class="col-7"><?php echo e($order->order_date?->format('d/m/Y')); ?></dd>

                    <dt class="col-5 text-muted">Cliente</dt>
                    <dd class="col-7"><?php echo e($order->user?->name ?? '-'); ?></dd>

                    <dt class="col-5 text-muted">Indirizzo</dt>
                    <dd class="col-7"><?php echo e($order->address); ?></dd>

                    <dt class="col-5 text-muted">Stato</dt>
                    <dd class="col-7">
                        <span class="badge badge-secondary" id="order-state-label"><?php echo e($order->stateLabel()); ?></span>
                    </dd>
                </dl>
                <div class="mt-3">
                    <button type="button" class="btn btn-success btn-sm btn-block"
                            id="btn-define-products"
                            title="Blocca l'ordine e conferma i prodotti">
                        <i class="fa fa-check mr-1"></i> Prodotti Definiti
                    </button>
                    <button type="button" class="btn btn-warning btn-sm btn-block"
                            id="btn-allocate-products"
                            title="Conferma l'allocazione ai magazzini">
                        <i class="fa fa-warehouse mr-1"></i> Prodotti Allocati
                    </button>
                    <a href="<?php echo e(route('customer-orders.index')); ?>" class="btn btn-secondary btn-sm btn-block mt-1">
                        <i class="fa fa-backward mr-1"></i> Indietro
                    </a>
                </div>
            </div>
        </div>
    </div>

    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header p-0">
                <ul class="nav nav-tabs" id="orderTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tab-products" data-toggle="tab"
                           href="#pane-products" role="tab">
                            <i class="fas fa-boxes mr-1"></i> Prodotti
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-summary" data-toggle="tab"
                           href="#pane-summary" role="tab">
                            <i class="fas fa-calculator mr-1"></i> Riepilogo
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">

                    
                    <div class="tab-pane fade show active" id="pane-products" role="tabpanel">

                <div class="d-flex justify-content-end mb-2">
                    <button type="button" class="btn btn-primary btn-sm <?php if(!$order->canBeModified()): ?> d-none <?php endif; ?>"
                            id="btn-add-product"
                            title="Aggiungi prodotto">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>

                        <table id="table_order_products" class="table table-hover table-sm" width="100%">
                            <thead>
                                <tr>
                                    <th style="width:30px;"></th>
                                    <th>Prodotto</th>
                                    <th class="text-right">Quantità</th>
                                    <th>U.M.</th>
                                    <th style="width:80px;">Azioni</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>

                    </div>

                    
                    <div class="tab-pane fade" id="pane-summary" role="tabpanel">
                        <div id="summary-loading" class="text-center py-3">
                            <i class="fas fa-spinner fa-spin"></i> Caricamento…
                        </div>
                        <div id="summary-body" class="d-none">
                            <table id="table_summary" class="table table-hover table-sm" width="100%">
                                <thead>
                                    <tr>
                                        <th>Prodotto</th>
                                        <th class="text-right">Quantità Totale</th>
                                        <th>U.M.</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div id="summary-empty" class="d-none text-muted text-center py-3">
                            <i class="fas fa-info-circle mr-1"></i>
                            Nessun dato da riepilogare.
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>


<div class="modal fade" id="modal-ingredients" tabindex="-1" role="dialog"
     aria-labelledby="modal-ingredients-label" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-ingredients-label">
                    <i class="fas fa-list-ul mr-1"></i> Ingredienti — <span id="ing-product-name"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Chiudi">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="modal-ing-errors" class="alert alert-danger d-none">
                    <ul class="mb-0" id="modal-ing-errors-list"></ul>
                </div>
                <div id="ing-loading" class="text-center py-3">
                    <i class="fas fa-spinner fa-spin"></i> Caricamento…
                </div>
                <div id="ing-body" class="d-none">
                    <p class="text-muted small mb-2">
                        Per ogni categoria ingrediente seleziona quale prodotto usare.
                    </p>
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Categoria</th>
                                <th class="text-right">Totale necessario</th>
                                <th class="text-right">Convertito in</th>
                                <th>Prodotto</th>
                            </tr>
                        </thead>
                        <tbody id="ing-rows"></tbody>
                    </table>
                </div>
                <div id="ing-empty" class="d-none text-muted text-center py-3">
                    <i class="fas fa-info-circle mr-1"></i>
                    Nessuna ricetta trovata per questo prodotto.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fa fa-times"></i> Annulla
                </button>
                <button type="button" class="btn btn-primary btn-sm d-none" id="btn-save-ingredients">
                    <i class="fa fa-save"></i> Salva
                </button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="modal-add-product" tabindex="-1" role="dialog"
     aria-labelledby="modal-add-product-label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-add-product-label">
                    <i class="fas fa-plus mr-1"></i> Aggiungi Prodotto
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Chiudi">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="modal-product-errors" class="alert alert-danger d-none">
                    <ul class="mb-0" id="modal-product-errors-list"></ul>
                </div>
                <input type="hidden" id="op_unit_of_measure_id" value="">

                <div class="form-group">
                    <label for="op_product_id">Prodotto <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-box"></i></span>
                        </div>
                        <select id="op_product_id" class="form-control">
                            <option value="">— Seleziona —</option>
                            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($p->id); ?>"
                                    data-uom-id="<?php echo e($p->productCategory?->unitOfMeasure?->id ?? ''); ?>"
                                    data-uom-symbol="<?php echo e($p->productCategory?->unitOfMeasure?->symbol ?? ''); ?>">
                                    <?php echo e($p->name); ?> (<?php echo e($p->typeLabel()); ?>)
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="op_qnt">Quantità <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" id="op_qnt" class="form-control"
                               placeholder="0,00" autocomplete="off">
                        <div class="input-group-append">
                            <span class="input-group-text" id="op-uom-addon"
                                  style="min-width:60px; justify-content:center;">—</span>
                        </div>
                    </div>
                    <small class="form-text text-muted">L'unità di misura è determinata dalla categoria del prodotto.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fa fa-times"></i> Annulla
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="btn-save-product">
                    <i class="fa fa-save"></i> Aggiungi
                </button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="modal-warehouse" tabindex="-1" role="dialog"
     aria-labelledby="modal-warehouse-label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-warehouse-label">
                    <i class="fas fa-warehouse mr-1"></i> Allocazione magazzini (per la produzione) — <span id="wh-product-name"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Chiudi">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="modal-wh-errors" class="alert alert-danger d-none">
                    <ul class="mb-0" id="modal-wh-errors-list"></ul>
                </div>
                <div id="wh-loading" class="text-center py-3">
                    <i class="fas fa-spinner fa-spin"></i> Caricamento…
                </div>
                <div id="wh-body" class="d-none">
                    <p class="text-muted small mb-2">
                        Quantità richiesta dall'ordine: <strong id="wh-order-qnt"></strong>
                    </p>
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Magazzino</th>
                                <th class="text-right">Quantità da allocare</th>
                            </tr>
                        </thead>
                        <tbody id="wh-rows"></tbody>
                    </table>
                </div>
                <div id="wh-empty" class="d-none text-muted text-center py-3">
                    <i class="fas fa-info-circle mr-1"></i>
                    Nessun magazzino disponibile.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fa fa-times"></i> Annulla
                </button>
                <button type="button" class="btn btn-primary btn-sm d-none" id="btn-save-warehouses">
                    <i class="fa fa-save"></i> Salva
                </button>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
<style>
    /* riga di dettaglio ingredienti */
    tr.detail-row td { background: #f8f9fa; padding: 8px 12px 8px 42px; }
    tr.detail-row .badge-ingredient { font-size: .8rem; margin: 2px; }
</style>
<script>
$(document).ready(function () {

    var csrfToken = $('meta[name="csrf-token"]').attr('content');
    var orderId   = <?php echo e($order->id); ?>;
    var canModify = <?php echo e($order->canBeModified() ? 'true' : 'false'); ?>;
    var orderState = '<?php echo e($order->state); ?>';

    // Visibilità pulsanti di stato: gestita a runtime
    function updateStateButtons() {
        if (orderState === '<?php echo e(\App\Models\CustomerOrder::STATE_CREATED); ?>') {
            $('#btn-define-products').removeClass('d-none');
            $('#btn-allocate-products').addClass('d-none');
        } else if (orderState === '<?php echo e(\App\Models\CustomerOrder::STATE_PRODUCTS_DEFINED); ?>') {
            $('#btn-define-products').addClass('d-none');
            if (<?php echo e($order->areAllProductsAllocated() ? 'true' : 'false'); ?>) {
                $('#btn-allocate-products').removeClass('d-none');
            } else {
                $('#btn-allocate-products').addClass('d-none');
            }
        } else if (orderState === '<?php echo e(\App\Models\CustomerOrder::STATE_PRODUCTS_ALLOCATED); ?>') {
            $('#btn-define-products').addClass('d-none');
            $('#btn-allocate-products').addClass('d-none');
        }
    }

    // Inizializza visibilità pulsanti al caricamento
    updateStateButtons();

    function formatIt(val, dec) {
        dec = dec === undefined ? 2 : dec;
        var n = parseFloat(val);
        if (isNaN(n)) return '';
        return n.toLocaleString('it-IT', { minimumFractionDigits: dec, maximumFractionDigits: dec });
    }

    // ================================================================
    // DataTable prodotti ordine
    // ================================================================
    var prodTable = $('#table_order_products').DataTable({
        order: [[1, 'asc']],
        pageLength: 25,
        ajax: {
            type: 'POST',
            url:  '/customer-orders/' + orderId + '/products/list/table',
            headers: { 'X-CSRF-TOKEN': csrfToken },
        },
        columns: [
            // 0 - expand toggle (no data)
            { data: null, defaultContent: '', orderable: false, searchable: false, className: 'dt-control text-center' },
            // 1 - prodotto
            { data: 'product_name',           name: 'product_name' },
            // 2 - quantità
            { data: 'qnt',                    name: 'qnt', className: 'text-right' },
            // 3 - u.m.
            { data: 'unit_of_measure_symbol', name: 'unit_of_measure_symbol', orderable: false },
            // 4 - azioni
            { data: 'id',                     name: 'id', orderable: false, searchable: false },
            // 5 - warehouses_allocated (hidden, used for rendering)
            { data: 'warehouses_allocated',   name: 'warehouses_allocated', orderable: false, searchable: false, visible: false },
        ],
        columnDefs: [
            {
                targets: 0,
                render: function () {
                    return '<i class="fas fa-chevron-right dt-chevron" style="cursor:pointer;transition:transform .2s;"></i>';
                }
            },
            {
                targets: 2,
                render: function (data) { return formatIt(data, 2); }
            },
            {
                targets: 4,
                render: function (id, type, row) {
                    if (canModify) {
                        return '<button class="btn btn-info btn-xs btn-config-op mr-1" data-id="' + id + '" title="Ingredienti">'
                             + '<i class="fas fa-list-ul"></i></button>'
                             + '<button class="btn btn-danger btn-xs btn-delete-op" data-id="' + id + '" title="Rimuovi">'
                             + '<i class="fa fa-trash"></i></button>';
                    }
                    if (orderState === 'products_allocated') return '';
                    var allocated = row.warehouses_allocated == 1;
                    var saved = allocated ? ' btn-success' : ' btn-outline-success';
                    var icon = allocated ? 'fa-check-circle' : 'fa-warehouse';
                    return '<button class="btn btn-xs btn-warehouse-op' + saved + '" data-id="' + id + '" title="Allocazione magazzini">'
                         + '<i class="fas ' + icon + '"></i></button>';
                }
            },
        ],
    });

    // ---- Expand / collapse riga dettagli --------------------------------
    function buildDetailRow(rowData) {
        var ingredientsHtml = rowData.details_html || '<span class="text-muted small">Nessun ingrediente configurato.</span>';
        var warehousesHtml = rowData.warehouses_html || '<span class="text-muted small">—</span>';
        return '<div style="padding:6px 8px 6px 32px;">'
             + '<span class="text-muted small mr-1"><i class="fas fa-cubes mr-1"></i>Ingredienti:</span> '
             + ingredientsHtml
             + '<br>'
             + '<span class="text-muted small mr-1"><i class="fas fa-warehouse mr-1"></i>Magazzini:</span> '
             + warehousesHtml
             + '</div>';
    }

    $('#table_order_products').on('click', 'td.dt-control', function () {
        var tr   = $(this).closest('tr');
        var row  = prodTable.row(tr);
        var icon = tr.find('.dt-chevron');

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
            icon.css('transform', 'rotate(0deg)');
        } else {
            row.child(buildDetailRow(row.data())).show();
            tr.addClass('shown');
            icon.css('transform', 'rotate(90deg)');
        }
    });

    // Apri automaticamente tutte le righe di dettaglio al caricamento
    prodTable.on('draw.dt', function () {
        setTimeout(function () {
            prodTable.rows().every(function () {
                var tr   = $(this.node());
                var row  = this;
                var icon = tr.find('.dt-chevron');
                if (!row.child.isShown()) {
                    row.child(buildDetailRow(row.data())).show();
                    tr.addClass('shown');
                    icon.css('transform', 'rotate(90deg)');
                }
            });
        }, 0);
    });

    // ================================================================
    // Tab Riepilogo
    // ================================================================
    var summaryTable = null;

    function loadSummary() {
        $('#summary-loading').removeClass('d-none');
        $('#summary-body').addClass('d-none');
        $('#summary-empty').addClass('d-none');

        $.ajax({
            url:     '/customer-orders/' + orderId + '/summary',
            type:    'GET',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function (response) {
                $('#summary-loading').addClass('d-none');

                if (!response.summaries || response.summaries.length === 0) {
                    $('#summary-empty').removeClass('d-none');
                    return;
                }

                // Costruisci le colonne dinamiche per i magazzini
                var columns = [
                    { data: 'product_name', name: 'product_name', title: 'Prodotto' },
                    { data: 'total_qnt', name: 'total_qnt', className: 'text-right', title: 'Quantità Totale', render: function (data) { return formatIt(data, 2); } },
                    { data: 'unit_of_measure_symbol', name: 'unit_of_measure_symbol', orderable: false, title: 'U.M.' },
                ];

                // Aggiungi colonne per ogni magazzino
                response.warehouses.forEach(function (warehouse) {
                    columns.push({
                        data: null,
                        name: 'warehouse_' + warehouse.id,
                        title: warehouse.name,
                        className: 'text-right',
                        orderable: false,
                        render: function (data, type, row) {
                            var stock = row.warehouse_stocks[warehouse.id];
                            if (!stock) return '-';
                            var qnt = formatIt(stock.qnt, 2);
                            var uom = row.unit_of_measure_symbol;
                            var text = qnt + ' ' + uom;
                            if (stock.is_negative) {
                                return '<span class="text-danger font-weight-bold">' + text + '</span>';
                            }
                            return text;
                        }
                    });
                });

                // Aggiorna l'header della tabella
                var thead = $('#table_summary thead').empty();
                var headerRow = $('<tr></tr>');
                columns.forEach(function (col) {
                    headerRow.append('<th>' + col.title + '</th>');
                });
                thead.append(headerRow);

                if (summaryTable) {
                    summaryTable.destroy();
                }

                summaryTable = $('#table_summary').DataTable({
                    data: response.summaries,
                    pageLength: 25,
                    order: [[0, 'asc']],
                    columns: columns,
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/it.json'
                    }
                });

                $('#summary-body').removeClass('d-none');
            },
            error: function () {
                $('#summary-loading').addClass('d-none');
                $('#summary-empty').removeClass('d-none').text('Errore durante il caricamento del riepilogo.');
            },
        });
    }

    $('#tab-summary').on('shown.bs.tab', function () {
        loadSummary();
    });

    // ================================================================
    // Modal aggiungi prodotto
    // ================================================================
    $('#btn-add-product').on('click', function () {
        resetAddModal();
        $('#modal-add-product').modal('show');
    });

    $('#op_product_id').on('change', function () {
        var opt = $(this).find('option:selected');
        $('#op_unit_of_measure_id').val(opt.data('uom-id') || '');
        $('#op-uom-addon').text(opt.data('uom-symbol') || '—');
    });

    $('#op_qnt').on('blur', function () {
        var n = parseFloat($(this).val().trim().replace(/\./g, '').replace(',', '.'));
        if (!isNaN(n)) $(this).val(formatIt(n, 2));
    });
    $('#op_qnt').on('keypress', function (e) {
        if (!/[\d,\.]/.test(String.fromCharCode(e.which))) e.preventDefault();
    });

    $('#btn-save-product').on('click', function () {
        hideAddErrors();
        $.ajax({
            url:     '/customer-orders/' + orderId + '/products',
            type:    'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: {
                product_id:         $('#op_product_id').val(),
                qnt:                $('#op_qnt').val().trim(),
                unit_of_measure_id: $('#op_unit_of_measure_id').val(),
            },
            success: function () {
                $('#modal-add-product').modal('hide');
                prodTable.ajax.reload(null, false);
            },
            error: function (xhr) {
                if (xhr.status === 422) showAddErrors(xhr.responseJSON.errors);
                else alert('Errore durante il salvataggio.');
            },
        });
    });

    // ---- Elimina prodotto -----------------------------------------------
    $('#table_order_products').on('click', '.btn-delete-op', function (e) {
        e.stopPropagation();
        if (!confirm('Rimuovere questo prodotto dall\'ordine?')) return;
        var id = $(this).data('id');
        $.ajax({
            url:     '/customer-orders/' + orderId + '/products/' + id,
            type:    'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function () { prodTable.ajax.reload(null, false); },
            error:   function () { alert('Errore durante la rimozione.'); },
        });
    });

    // ================================================================
    // Modal ingredienti
    // ================================================================
    var currentOrderProductId = null;

    $('#table_order_products').on('click', '.btn-config-op', function (e) {
        e.stopPropagation();
        currentOrderProductId = $(this).data('id');

        $('#ing-product-name').text('');
        $('#ing-rows').empty();
        $('#ing-loading').removeClass('d-none');
        $('#ing-body').addClass('d-none');
        $('#ing-empty').addClass('d-none').text('Nessuna ricetta trovata per questo prodotto.');
        $('#btn-save-ingredients').addClass('d-none');
        $('#modal-ing-errors').addClass('d-none');
        $('#modal-ing-errors-list').empty();

        $('#modal-ingredients').modal('show');

        $.ajax({
            url:     '/customer-orders/' + orderId + '/products/' + currentOrderProductId + '/details/config',
            type:    'GET',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function (data) {
                $('#ing-loading').addClass('d-none');
                $('#ing-product-name').text(data.product_name);

                if (!data.rows || data.rows.length === 0) {
                    $('#ing-empty').removeClass('d-none');
                    return;
                }

                var tbody = $('#ing-rows').empty();

                // Funzione ricorsiva per aggiungere righe
                function addRecipeRows(rows, depth) {
                    $.each(rows, function (i, row) {
                        var opts = '<option value="">— Seleziona —</option>';
                        $.each(row.products, function (j, p) {
                            var sel = (row.selected_product_id == p.id) ? ' selected' : '';
                            var typeLabel = p.type === 'semi_finished' ? ' (Semi)' : '';
                            opts += '<option value="' + p.id + '"' + sel + '>'
                                  + $('<span>').text(p.name + typeLabel).html()
                                  + '</option>';
                        });

                        // totale con conversione
                        var totalFmt = parseFloat(row.total).toLocaleString('it-IT', {minimumFractionDigits:2, maximumFractionDigits:4});
                        var totalCell = totalFmt + ' ' + $('<span>').text(row.recipe_uom_symbol).html();

                        // convertito in (dall'unità ricetta all'unità categoria) - mostra solo se diverso
                        var conversionCell = '';
                        if (row.recipe_uom_symbol !== row.category_uom_symbol || Math.abs(row.total - row.converted_total) > 0.0001) {
                            var convertedTotalFmt = parseFloat(row.converted_total).toLocaleString('it-IT', {minimumFractionDigits:2, maximumFractionDigits:4});
                            conversionCell = totalFmt + ' ' + $('<span>').text(row.recipe_uom_symbol).html() + ' → ' + convertedTotalFmt + ' ' + $('<span>').text(row.category_uom_symbol).html();
                        }

                        // Indentazione basata sulla profondità
                        var indent = depth * 20;
                        var categoryCell = '<span style="margin-left:' + indent + 'px;">' + $('<span>').text(row.category_name).html() + '</span>';
                        if (depth > 0) {
                            categoryCell = '<i class="fas fa-angle-right text-muted mr-1"></i>' + categoryCell;
                        }

                        tbody.append(
                            '<tr data-recipe-id="' + row.recipe_id + '" '
                          + 'data-depth="' + depth + '" '
                          + 'data-original-qnt="' + row.original_qnt + '" '
                          + 'data-original-unit-of-measure-id="' + row.original_unit_of_measure_id + '" '
                          + 'data-conversion-qnt="' + row.conversion_qnt + '" '
                          + 'data-conversion-unit-of-measure-id="' + row.conversion_unit_of_measure_id + '">'
                          + '<td>' + categoryCell + '</td>'
                          + '<td class="text-right">' + totalCell + '</td>'
                          + '<td class="text-right small">' + conversionCell + '</td>'
                          + '<td><select class="form-control form-control-sm ing-product-select">' + opts + '</select></td>'
                          + '</tr>'
                        );

                        // Ricorsione per ricette annidate
                        if (row.nested_recipes && row.nested_recipes.length > 0) {
                            addRecipeRows(row.nested_recipes, depth + 1);
                        }
                    });
                }

                addRecipeRows(data.rows, 0);

                $('#ing-body').removeClass('d-none');
                $('#btn-save-ingredients').removeClass('d-none');
            },
            error: function (xhr) {
                $('#ing-loading').addClass('d-none');
                var msg = 'Errore durante il caricamento.';
                if (xhr.responseJSON && xhr.responseJSON.message) msg += ' (' + xhr.responseJSON.message + ')';
                $('#ing-empty').removeClass('d-none').text(msg);
            },
        });
    });

    $('#btn-save-ingredients').on('click', function () {
        var selections = [];
        var valid = true;

        $('#ing-rows tr').each(function () {
            var recipeId  = $(this).data('recipe-id');
            var productId = $(this).find('.ing-product-select').val();
            if (!productId) { valid = false; return false; }
            selections.push({
                recipe_id: recipeId,
                product_id: productId,
                original_qnt: $(this).data('original-qnt'),
                original_unit_of_measure_id: $(this).data('original-unit-of-measure-id'),
                conversion_qnt: $(this).data('conversion-qnt'),
                conversion_unit_of_measure_id: $(this).data('conversion-unit-of-measure-id'),
            });
        });

        if (!valid) {
            $('#modal-ing-errors-list').html('<li>Seleziona un prodotto per ogni ingrediente.</li>');
            $('#modal-ing-errors').removeClass('d-none');
            return;
        }
        $('#modal-ing-errors').addClass('d-none');

        $.ajax({
            url:         '/customer-orders/' + orderId + '/products/' + currentOrderProductId + '/details',
            type:        'POST',
            contentType: 'application/json',
            headers:     { 'X-CSRF-TOKEN': csrfToken },
            data:        JSON.stringify({ selections: selections }),
            success: function () {
                $('#modal-ingredients').modal('hide');
                prodTable.ajax.reload(null, false);
                // Ricarica il riepilogo se la tab è attiva
                if ($('#tab-summary').hasClass('active')) {
                    loadSummary();
                }
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    var list = $('#modal-ing-errors-list').empty();
                    $.each(xhr.responseJSON.errors, function (f, msgs) {
                        $.each(msgs, function (i, msg) { list.append('<li>' + msg + '</li>'); });
                    });
                    $('#modal-ing-errors').removeClass('d-none');
                } else {
                    alert('Errore durante il salvataggio.');
                }
            },
        });
    });

    // ================================================================
    // Cambio stato: "Definisci Prodotti"
    // ================================================================
    $('#btn-define-products').on('click', function () {
        if (!confirm('Confermi di voler definire i prodotti? Dopo questa operazione non sarà più possibile modificare l\'ordine.')) return;

        $.ajax({
            url:     '/customer-orders/' + orderId + '/state',
            type:    'PUT',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data:    { state: 'products_defined' },
            success: function (response) {
                // Aggiorna etichetta stato
                $('#order-state-label').text(response.state_label);

                // Aggiorna variabile stato
                orderState = '<?php echo e(\App\Models\CustomerOrder::STATE_PRODUCTS_DEFINED); ?>';

                // Aggiorna visibilità pulsanti
                updateStateButtons();

                // Aggiorna flag per nascondere pulsanti elimina
                canModify = false;

                // Ricarica tabella prodotti per aggiornare le azioni
                prodTable.ajax.reload(null, false);
            },
            error: function (xhr) {
                var msg = 'Errore durante il cambio di stato.';
                if (xhr.responseJSON && xhr.responseJSON.message) msg += ' (' + xhr.responseJSON.message + ')';
                alert(msg);
            },
        });
    });

    // ================================================================
    // Cambio stato: "Prodotti Allocati"
    // ================================================================
    $('#btn-allocate-products').on('click', function () {
        if (!confirm('Confermi che tutti i prodotti sono allocati ai magazzini? Dopo questa operazione non sarà più possibile modificare l\'allocazione.')) return;

        $.ajax({
            url:     '/customer-orders/' + orderId + '/state',
            type:    'PUT',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data:    { state: 'products_allocated' },
            success: function (response) {
                // Aggiorna etichetta stato
                $('#order-state-label').text(response.state_label);

                // Aggiorna variabile stato
                orderState = '<?php echo e(\App\Models\CustomerOrder::STATE_PRODUCTS_ALLOCATED); ?>';

                // Aggiorna visibilità pulsanti
                updateStateButtons();

                // Ricarica tabella prodotti per aggiornare le azioni
                prodTable.ajax.reload(null, false);
            },
            error: function (xhr) {
                var msg = 'Errore durante il cambio di stato.';
                if (xhr.responseJSON && xhr.responseJSON.message) msg += ' (' + xhr.responseJSON.message + ')';
                alert(msg);
            },
        });
    });

    // ================================================================
    // Modal allocazione magazzini
    // ================================================================
    var currentWarehouseProductId = null;
    var currentWarehouseOrderQnt = 0;
    var savedWarehouses = {}; // { productId: true }

    $('#table_order_products').on('click', '.btn-warehouse-op', function (e) {
        e.stopPropagation();
        currentWarehouseProductId = $(this).data('id');

        $('#wh-product-name').text('');
        $('#wh-rows').empty();
        $('#wh-loading').removeClass('d-none');
        $('#wh-body').addClass('d-none');
        $('#wh-empty').addClass('d-none');
        $('#btn-save-warehouses').addClass('d-none');
        $('#modal-wh-errors').addClass('d-none');
        $('#modal-wh-errors-list').empty();

        $('#modal-warehouse').modal('show');

        $.ajax({
            url:     '/customer-orders/' + orderId + '/products/' + currentWarehouseProductId + '/warehouses',
            type:    'GET',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function (data) {
                $('#wh-loading').addClass('d-none');
                $('#wh-product-name').text(data.product_name);
                $('#wh-order-qnt').text(formatIt(data.order_qnt, 2) + ' ' + data.uom_symbol);
                currentWarehouseOrderQnt = data.order_qnt;

                if (!data.rows || data.rows.length === 0) {
                    $('#wh-empty').removeClass('d-none');
                    return;
                }

                var tbody = $('#wh-rows').empty();
                $.each(data.rows, function (i, row) {
                    tbody.append(
                        '<tr>'
                        + '<td>' + $('<span>').text(row.warehouse_name).html() + '</td>'
                        + '<td class="text-right">'
                        + '<input type="number" class="form-control form-control-sm wh-qnt-input text-right"'
                        + ' step="0.01" min="0" style="width:120px;display:inline-block;"'
                        + ' data-wh-id="' + row.warehouse_id + '"'
                        + ' value="' + formatIt(row.qnt, 2).replace(/\./g, '').replace(',', '.') + '">'
                        + '</td>'
                        + '</tr>'
                    );
                });

                $('#wh-body').removeClass('d-none');
                $('#btn-save-warehouses').removeClass('d-none');
            },
            error: function (xhr) {
                $('#wh-loading').addClass('d-none');
                var msg = 'Errore durante il caricamento.';
                if (xhr.responseJSON && xhr.responseJSON.message) msg += ' (' + xhr.responseJSON.message + ')';
                $('#wh-empty').removeClass('d-none').text(msg);
            },
        });
    });

    $('#btn-save-warehouses').on('click', function () {
        var allocations = [];
        var total = 0;
        $('#wh-rows tr').each(function () {
            var input = $(this).find('.wh-qnt-input');
            var val = parseFloat(input.val().replace(',', '.'));
            if (isNaN(val)) val = 0;
            total += val;
            allocations.push({
                warehouse_id: parseInt(input.data('wh-id')),
                qnt: val,
            });
        });

        // Verifica che la somma corrisponda alla quantità richiesta
        if (Math.abs(total - currentWarehouseOrderQnt) > 0.01) {
            $('#modal-wh-errors-list').html(
                '<li>La somma delle quantità allocate (' + formatIt(total, 2) + ') non corrisponde alla quantità richiesta dall\'ordine (' + formatIt(currentWarehouseOrderQnt, 2) + ').</li>'
            );
            $('#modal-wh-errors').removeClass('d-none');
            return;
        }

        $('#modal-wh-errors').addClass('d-none');

        $.ajax({
            url:         '/customer-orders/' + orderId + '/products/' + currentWarehouseProductId + '/warehouses',
            type:        'POST',
            contentType: 'application/json',
            headers:     { 'X-CSRF-TOKEN': csrfToken },
            data:        JSON.stringify({ allocations: allocations }),
            success: function () {
                savedWarehouses[currentWarehouseProductId] = true;
                $('#modal-warehouse').modal('hide');
                // Ricarica tabella per aggiornare l'icona del pulsante
                prodTable.ajax.reload(null, false);
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    var list = $('#modal-wh-errors-list').empty();
                    $.each(xhr.responseJSON.errors, function (f, msgs) {
                        $.each(msgs, function (i, msg) { list.append('<li>' + msg + '</li>'); });
                    });
                    $('#modal-wh-errors').removeClass('d-none');
                } else {
                    alert('Errore durante il salvataggio.');
                }
            },
        });
    });

    // ================================================================
    // Helpers
    // ================================================================
    function resetAddModal() {
        $('#op_product_id, #op_qnt, #op_unit_of_measure_id').val('');
        $('#op-uom-addon').text('—');
        hideAddErrors();
    }
    function showAddErrors(errors) {
        var list = $('#modal-product-errors-list').empty();
        $.each(errors, function (f, msgs) {
            $.each(msgs, function (i, msg) { list.append('<li>' + msg + '</li>'); });
        });
        $('#modal-product-errors').removeClass('d-none');
    }
    function hideAddErrors() {
        $('#modal-product-errors').addClass('d-none');
        $('#modal-product-errors-list').empty();
    }

});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\project\shara_light\backend\resources\views\customer_order\show.blade.php ENDPATH**/ ?>