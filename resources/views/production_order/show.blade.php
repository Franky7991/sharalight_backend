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

});
</script>
@stop