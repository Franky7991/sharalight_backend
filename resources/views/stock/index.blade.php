@extends('adminlte::page')
@section('title', 'Giacenza')
@section('content_header')@stop

@section('content')
<div class="card">
    <div class="card-header pb-0">
        <h4 class="mb-0">Giacenza</h4>
    </div>
    <div class="card-body">
        <table id="table_stock" class="table table-hover" width="100%">
            <thead>
                <tr>
                    <th>Magazzino</th>
                    <th>Prodotto</th>
                    <th class="text-right">Quantità</th>
                    <th>U.M.</th>
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

    function formatIt(val) {
        var n = parseFloat(val);
        if (isNaN(n)) return '-';
        return n.toLocaleString('it-IT', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    $('#table_stock').DataTable({
        order: [[0, 'asc'], [1, 'asc']],
        pageLength: 25,
        ajax: {
            type: 'POST',
            url: '{{ route('stocks.datatable') }}',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        },
        columns: [
            { data: 'warehouse_name',         name: 'warehouse_name' },
            { data: 'product_name',           name: 'product_name' },
            { data: 'qnt',                    name: 'qnt', className: 'text-right' },
            { data: 'unit_of_measure_symbol', name: 'unit_of_measure_symbol', orderable: false },
        ],
        columnDefs: [
            {
                targets: 2,
                render: function (data, type, row) {
                    if (type !== 'display') return data;
                    var n      = parseFloat(data);
                    var cls    = n < 0 ? ' text-danger font-weight-bold' : (n === 0 ? ' text-muted' : '');
                    return '<span class="' + cls + '">' + formatIt(data) + '</span>';
                }
            },
        ],
    });

});
</script>
@stop
