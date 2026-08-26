@extends('adminlte::page')
@section('title', 'Spedizione ' . $shipment->progressive)
@section('content_header')@stop

@section('content')
<div class="row">

    <div class="col-md-4">
        <div class="card">
            <div class="card-header pb-0">
                <h4 class="mb-0">Dati Spedizione</h4>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5 text-muted">Progressivo</dt>
                    <dd class="col-7">{{ $shipment->progressive }}</dd>

                    <dt class="col-5 text-muted">Data Spedizione</dt>
                    <dd class="col-7">{{ $shipment->date?->format('d/m/Y') }}</dd>

                    <dt class="col-5 text-muted">Stato</dt>
                    <dd class="col-7">
                        <span class="badge badge-secondary" id="shipment-state-label">{{ $shipment->stateLabel() }}</span>
                    </dd>
                </dl>
                <div class="mt-3">
                    <a href="{{ route('shipments.index') }}" class="btn btn-secondary btn-sm btn-block">
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

                @if($shipment->isCreated())
                <div class="row mb-3">
                    <div class="col-9">
                        <select id="order-select" class="form-control">
                            <option value="">-- Seleziona un ordine cliente --</option>
                            @foreach($availableOrders as $a)
                                <option value="{{ $a->id }}">
                                    @php $pct = (int) round($a->productionProgress()); @endphp
                                    {{ $a->progressive }} — {{ $a->user?->name ?? '?' }}
                                    ({{ number_format((float)$a->qnt, 2, ',', '.') }} — {{ $pct }}%)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-3">
                        <button type="button" class="btn btn-success btn-block" id="btn-add-order">
                            <i class="fa fa-plus mr-1"></i> Aggiungi
                        </button>
                    </div>
                </div>
                @endif

                <table id="table_shipment_orders" class="table table-hover" width="100%">
                    <thead>
                        <tr>
                            <th>Progressivo</th>
                            <th>Data Ordine</th>
                            <th>Indirizzo</th>
                            <th style="width:60px;">Azioni</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ================================================================
     Prodotti degli ordini clienti inseriti in spedizione
     ================================================================ --}}
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
                    <th>Composizione</th>
                    <th class="text-right">Quantità</th>
                    <th>U.M.</th>
                    <th class="text-right">Q.ta Prodotta</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function () {

    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    var ordersTable = $('#table_shipment_orders').DataTable({
        order: [[0, 'asc']],
        pageLength: 25,
        ajax: {
            type: 'POST',
            url: '{{ route('shipment-details.datatable', $shipment->id) }}',
            headers: { 'X-CSRF-TOKEN': csrfToken },
        },
        columns: [
            { data: 'progressive',     name: 'progressive' },
            { data: 'order_date_fmt',  name: 'order_date_fmt', orderable: false },
            { data: 'address',         name: 'address', orderable: false },
            { data: 'id',              name: 'id', orderable: false, searchable: false },
        ],
        columnDefs: [
            {
                targets: 3,
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
            url: '{{ route('shipment-products.datatable', $shipment->id) }}',
            headers: { 'X-CSRF-TOKEN': csrfToken },
        },
        columns: [
            { data: 'order_progressive',      name: 'order_progressive' },
            { data: 'product_name',           name: 'product_name' },
            { data: 'composition_html',       name: 'composition_html', orderable: false },
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
            url: '{{ route('shipment-details.store', $shipment->id) }}',
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
    var destroyUrlBase = '{{ route('shipment-details.destroy', [$shipment->id, '__DETAIL__']) }}';
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
@stop