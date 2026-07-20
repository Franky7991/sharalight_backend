@extends('adminlte::page')
@section('title', 'Prodotti')
@section('content_header')@stop

@section('content')
<div class="card">
    <div class="card-header pb-0">
        <h4 class="mb-0">Prodotti</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="row">
                    <div class="col-6"></div>
                    <div class="col-3">
                        <button type="button" class="btn btn-danger btn-block btn-sm js-delete"
                            data-list="table_list" data-url="{{ route('products.delete') }}">
                            <i class="fa fa-trash"></i> Cancella
                        </button>
                    </div>
                    <div class="col-3">
                        <a href="{{ route('products.create') }}">
                            <button type="button" class="btn btn-primary btn-block btn-sm">
                                <i class="fa fa-plus"></i> Nuovo
                            </button>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <table id="table_list" class="table table-hover" width="100%">
                    <thead>
                        <tr>
                            <th><input class="form-check-input" type="checkbox" onClick="toggle(this, 'selected[]')"></th>
                            <th>Nome</th>
                            <th>Categoria</th>
                            <th>Tipo</th>
                            <th>Azioni</th>
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

    $(document).on('click', '.btn_edit', function () {
        var url = "{{ route('products.show', ['_id_']) }}";
        window.location.href = url.replace('_id_', $(this).data('id'));
    });

    $("#table_list").DataTable({
        order: [1, 'asc'],
        pageLength: -1,
        ajax: {
            type: 'POST',
            url: '{{ route('products.datatable') }}',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: {},
        },
        columns: [
            { searchable: false, orderable: false, data: null, defaultContent: "", class: "disableEdit" },
            { data: "name",                  name: "name" },
            { data: "product_category_name", name: "product_category_name" },
            { data: "type_label",            name: "type_label" },
            { data: "id",                    name: "id" },
        ],
        columnDefs: [
            {
                targets: 0,
                render: function (data, type, row) {
                    return '<div class="form-check"><input class="form-check-input" type="checkbox" name="selected[]" value="' + row.id + '"></div>';
                }
            },
            {
                targets: 4,
                render: function (data) {
                    return '<button type="button" class="btn btn-primary btn-sm btn_edit" data-id="' + data + '"><i class="fa fa-edit"></i></button>';
                }
            },
        ],
    });

});
</script>
@stop
