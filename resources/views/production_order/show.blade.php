@extends('adminlte::page')
@section('title', 'Ordine di Produzione ' . $productionOrder->progressive)
@section('content_header')@stop

@section('content')
<div class="row">

    <div class="col-md-4">
        <div class="card">
            <div class="card-header pb-0">
                <h4 class="mb-0">Dati Ordine di Produzione</h4>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5 text-muted">Progressivo</dt>
                    <dd class="col-7">{{ $productionOrder->progressive }}</dd>

                    <dt class="col-5 text-muted">Data Produzione</dt>
                    <dd class="col-7">{{ $productionOrder->production_date?->format('d/m/Y') }}</dd>

                    <dt class="col-5 text-muted">Magazzino</dt>
                    <dd class="col-7">{{ $productionOrder->warehouse?->name ?? '-' }}</dd>

                    <dt class="col-5 text-muted">Stato</dt>
                    <dd class="col-7">
                        <span class="badge badge-secondary" id="order-state-label">{{ $productionOrder->stateLabel() }}</span>
                    </dd>
                </dl>
                <div class="mt-3">
                    @if($productionOrder->isCreated())
                        <button type="button" class="btn btn-warning btn-sm btn-block" id="btn-in-processing">
                            <i class="fa fa-play mr-1"></i> In Lavorazione
                        </button>
                    @endif
                    <a href="{{ route('production-orders.index') }}" class="btn btn-secondary btn-sm btn-block mt-1">
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

                @if($productionOrder->isCreated())
                <div class="row mb-3">
                    <div class="col-9">
                        <select id="detail-select" class="form-control">
                            <option value="">-- Seleziona una riga ordine cliente --</option>
                            @foreach($available as $a)
                                <option value="{{ $a->id }}">
                                    {{ $a->customerOrder?->progressive ?? '-' }} — {{ $a->product?->name ?? '-' }}
                                    ({{ number_format((float)$a->qnt, 2, ',', '.') }} {{ $a->unitOfMeasure?->symbol ?? '' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-3">
                        <button type="button" class="btn btn-primary btn-block btn-sm" id="btn-add-detail">
                            <i class="fa fa-plus"></i> Aggiungi
                        </button>
                    </div>
                </div>
                @endif

                @if($productionOrder->isCreated() && $available->isEmpty())
                    <div class="alert alert-info small mb-3">
                        <i class="fas fa-info-circle mr-1"></i>
                        Nessuna riga disponibile: servono ordini cliente nello stato "Prodotti Allocati".
                    </div>
                @endif

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
@if(!$productionOrder->isCreated())
<div class="row mt-2">
    <div class="col-12">
        <div class="card">
            <div class="card-header pb-0">
                <div class="d-flex align-items-center justify-content-between">
                    <h4 class="mb-0"><i class="fas fa-industry mr-1"></i> Produzione</h4>
                </div>
            </div>
            <div class="card-body">

                @if($productionOrder->isCompleted())
                    <div class="alert alert-success mb-3">
                        <i class="fas fa-check-circle mr-1"></i> Produzione completata.
                    </div>
                @endif

                <h6 class="text-uppercase text-muted font-weight-bold mb-2" style="font-size:.75rem; letter-spacing:.05em;">
                    <i class="fas fa-box mr-1"></i> Prodotti da produrre
                </h6>

                @if(count($plan['products']))
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
                            @foreach($plan['products'] as $p)
                                <tr>
                                    <td>{{ $p['product_name'] }}</td>
                                    <td class="small">
                                        @if(count($p['ingredients']))
                                            <ul class="list-unstyled mb-0">
                                                @foreach($p['ingredients'] as $ing)
                                                    <li>{{ $ing['product_name'] }} — {{ number_format($ing['qnt'], 2, ',', '.') }} {{ $ing['uom_symbol'] }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-right">{{ number_format($p['qnt'], 2, ',', '.') }}</td>
                                    <td class="text-right font-weight-bold {{ $p['remaining'] > 0 ? 'text-warning' : 'text-success' }}">
                                        {{ number_format($p['qnt_produced'], 2, ',', '.') }}
                                    </td>
                                    <td>{{ $p['uom_symbol'] }}</td>
                                    <td>
                                        @if($productionOrder->isInProcessing() && $p['remaining'] > 0)
                                            <button type="button" class="btn btn-success btn-sm btn-produce-product"
                                                    data-detail-id="{{ $p['id'] }}"
                                                    data-product="{{ $p['product_name'] }}"
                                                    data-uom="{{ $p['uom_symbol'] }}"
                                                    data-remaining="{{ $p['remaining'] }}">
                                                <i class="fa fa-play mr-1"></i> Produci
                                            </button>
                                        @elseif($p['remaining'] <= 0)
                                            <span class="badge badge-success">Completato</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-muted small">Nessun prodotto da produrre.</p>
                @endif

                <h6 class="text-uppercase text-muted font-weight-bold mb-2" style="font-size:.75rem; letter-spacing:.05em;">
                    <i class="fas fa-cubes mr-1"></i> Materie prime necessarie
                </h6>

                @if($plan['missing_count'] > 0)
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        <strong>{{ $plan['missing_count'] }}</strong> materiale/i mancante/i a magazzino.
                    </div>
                @endif

                @if(count($plan['materials']))
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Materiale</th>
                                <th class="text-right">Richiesta</th>
                                <th class="text-right">Giacenza</th>
                                <th class="text-right">Mancante</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($plan['materials'] as $m)
                                <tr class="{{ $m['is_missing'] ? 'table-danger' : '' }}">
                                    <td>{{ $m['product_name'] }}</td>
                                    <td class="text-right">{{ number_format($m['required_qnt'], 2, ',', '.') }} {{ $m['uom_symbol'] }}</td>
                                    <td class="text-right">{{ number_format($m['available_qnt'], 2, ',', '.') }} {{ $m['uom_symbol'] }}</td>
                                    <td class="text-right {{ $m['is_missing'] ? 'text-danger font-weight-bold' : 'text-muted' }}">
                                        {{ number_format($m['missing_qnt'], 2, ',', '.') }} {{ $m['uom_symbol'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-muted small mb-0">Nessuna materia prima richiesta.</p>
                @endif

                <h6 class="text-uppercase text-muted font-weight-bold mb-2 mt-4" style="font-size:.75rem; letter-spacing:.05em;">
                    <i class="fas fa-history mr-1"></i> Produzioni registrate
                </h6>

                @if($productionOrder->records && $productionOrder->records->count())
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Data</th>
                                <th>Prodotto</th>
                                <th class="text-right">Quantità</th>
                                <th>U.M.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($productionOrder->records as $rec)
                                <tr>
                                    <td>{{ $rec->created_at?->format('d/m/Y H:i') }}</td>
                                    <td>{{ $rec->product?->name ?? '-' }}</td>
                                    <td class="text-right">{{ number_format((float)$rec->qnt, 2, ',', '.') }}</td>
                                    <td>{{ $rec->unitOfMeasure?->symbol ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-muted small mb-0">Nessuna produzione registrata.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

{{-- Modal: produzione prodotto --}}
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

@stop
@section('js')
<script>
$(document).ready(function () {

    var csrfToken = $('meta[name="csrf-token"]').attr('content');
    var productionOrderId = "{{ $productionOrder->id }}";
    var canModify = {{ $productionOrder->isCreated() ? 'true' : 'false' }};

    var detailsTable = $('#table_details').DataTable({
        order: [[0, 'asc']],
        pageLength: -1,
        ajax: {
            type: 'POST',
            url: '{{ route('production-order-details.datatable', $productionOrder->id) }}',
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
            url: '{{ route('production-orders.change-state', $productionOrder->id) }}',
            type: 'PUT',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: { state: '{{ \App\Models\ProductionOrder::STATE_IN_PROCESSING }}' },
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
            url: '{{ route('production-order-details.store', $productionOrder->id) }}',
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
            $tbody.html('<tr><td colspan="4" class="text-center text-muted">Indicare una quantità da produrre.</td></tr>');
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
                        rows += '<tr class="' + cls + '">'
                              + '<td>' + $('<span>').text(m.product_name).html() + '</td>'
                              + '<td class="text-right">' + formatIt(m.required_qnt) + ' ' + m.uom_symbol + '</td>'
                              + '<td class="text-right">' + formatIt(m.available_qnt) + ' ' + m.uom_symbol + '</td>'
                              + '<td class="text-right ' + missingCls + '">' + formatIt(m.missing_qnt) + ' ' + m.uom_symbol + '</td>'
                              + '</tr>';
                    });
                } else {
                    rows = '<tr><td colspan="4" class="text-center text-muted">Nessuna materia prima richiesta.</td></tr>';
                }
                $tbody.html(rows);
            },
            error: function () {
                $status.html('<span class="text-danger">Errore nel caricamento della giacenza.</span>');
                $btn.prop('disabled', true);
                $tbody.html('<tr><td colspan="4" class="text-center text-muted">Errore nel caricamento.</td></tr>');
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
        $('#produce-materials-table').html('<tr><td colspan="4" class="text-center text-muted">Caricamento giacenza…</td></tr>');
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
            url: '{{ route('production-orders.produce', $productionOrder->id) }}',
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
@stop