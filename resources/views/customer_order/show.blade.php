@extends('adminlte::page')
@section('title', 'Ordine ' . $order->progressive)
@section('content_header')@stop

@section('content')
<div class="row">

    {{-- ================================================================
         Colonna sinistra: dati ordine
         ================================================================ --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header pb-0">
                <h4 class="mb-0">Dati Ordine</h4>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5 text-muted">Progressivo</dt>
                    <dd class="col-7">{{ $order->progressive }}</dd>

                    <dt class="col-5 text-muted">Data Ordine</dt>
                    <dd class="col-7">{{ $order->order_date?->format('d/m/Y') }}</dd>

                    <dt class="col-5 text-muted">Cliente</dt>
                    <dd class="col-7">{{ $order->user?->name ?? '-' }}</dd>

                    <dt class="col-5 text-muted">Indirizzo</dt>
                    <dd class="col-7">{{ $order->address }}</dd>

                    <dt class="col-5 text-muted">Stato</dt>
                    <dd class="col-7">
                        <span class="badge badge-secondary">{{ $order->stateLabel() }}</span>
                    </dd>
                </dl>
                <div class="mt-3">
                    <a href="{{ route('customer-orders.index') }}" class="btn btn-secondary btn-sm btn-block">
                        <i class="fa fa-backward mr-1"></i> Indietro
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================
         Colonna destra: tabs
         ================================================================ --}}
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
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">

                    {{-- ---- Tab Prodotti ---- --}}
                    <div class="tab-pane fade show active" id="pane-products" role="tabpanel">

                        <div class="d-flex justify-content-end mb-2">
                            <button type="button" class="btn btn-primary btn-sm" id="btn-add-product"
                                    title="Aggiungi prodotto">
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>

                        <table id="table_order_products" class="table table-hover table-sm" width="100%">
                            <thead>
                                <tr>
                                    <th style="width:30px;"></th>{{-- expand --}}
                                    <th>Prodotto</th>
                                    <th class="text-right">Quantità</th>
                                    <th>U.M.</th>
                                    <th style="width:80px;">Azioni</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>

                    </div>

                </div>
            </div>
        </div>
    </div>

</div>

{{-- ================================================================
     Modal: ingredienti (CustomerOrderHasProductDetail)
     ================================================================ --}}
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
                                <th class="text-right" style="width:90px;">Qtà</th>
                                <th style="width:60px;">U.M.</th>
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

{{-- ================================================================
     Modal: aggiungi prodotto all'ordine
     ================================================================ --}}
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
                            @foreach($products as $p)
                                <option value="{{ $p->id }}"
                                    data-uom-id="{{ $p->productCategory?->unitOfMeasure?->id ?? '' }}"
                                    data-uom-symbol="{{ $p->productCategory?->unitOfMeasure?->symbol ?? '' }}">
                                    {{ $p->name }} ({{ $p->typeLabel() }})
                                </option>
                            @endforeach
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
@stop

@section('js')
<style>
    /* riga di dettaglio ingredienti */
    tr.detail-row td { background: #f8f9fa; padding: 8px 12px 8px 42px; }
    tr.detail-row .badge-ingredient { font-size: .8rem; margin: 2px; }
</style>
<script>
$(document).ready(function () {

    var csrfToken = $('meta[name="csrf-token"]').attr('content');
    var orderId   = {{ $order->id }};

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
                render: function (id) {
                    return '<button class="btn btn-info btn-xs btn-config-op mr-1" data-id="' + id + '" title="Ingredienti">'
                         + '<i class="fas fa-list-ul"></i></button>'
                         + '<button class="btn btn-danger btn-xs btn-delete-op" data-id="' + id + '" title="Rimuovi">'
                         + '<i class="fa fa-trash"></i></button>';
                }
            },
        ],
    });

    // ---- Expand / collapse riga dettagli --------------------------------
    function buildDetailRow(rowData) {
        var html = rowData.details_html || '<span class="text-muted small">Nessun ingrediente configurato.</span>';
        return '<div style="padding:6px 8px 6px 32px;">'
             + '<span class="text-muted small mr-1"><i class="fas fa-cubes mr-1"></i>Ingredienti:</span> '
             + html
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
                $.each(data.rows, function (i, row) {
                    var opts = '<option value="">— Seleziona —</option>';
                    $.each(row.products, function (j, p) {
                        var sel = (row.selected_product_id == p.id) ? ' selected' : '';
                        opts += '<option value="' + p.id + '"' + sel + '>'
                              + $('<span>').text(p.name).html()
                              + '</option>';
                    });
                    tbody.append(
                        '<tr data-recipe-id="' + row.recipe_id + '">'
                      + '<td>' + $('<span>').text(row.category_name).html() + '</td>'
                      + '<td class="text-right">'
                      + parseFloat(row.quantity).toLocaleString('it-IT', {minimumFractionDigits:2, maximumFractionDigits:2})
                      + '</td>'
                      + '<td>' + $('<span>').text(row.uom_symbol).html() + '</td>'
                      + '<td><select class="form-control form-control-sm ing-product-select">' + opts + '</select></td>'
                      + '</tr>'
                    );
                });

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
            selections.push({ recipe_id: recipeId, product_id: productId });
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
@stop
