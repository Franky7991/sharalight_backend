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

{{-- Modal grafo ingredienti --}}
<div class="modal fade" id="modal-product-tree" tabindex="-1" role="dialog"
     aria-labelledby="modal-product-tree-label" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content" style="border-radius: 6px; overflow: hidden;">
            <div class="modal-header" style="background: #1e2532; border-bottom: none; padding: 14px 20px;">
                <h6 class="modal-title mb-0" id="modal-product-tree-label"
                    style="color: #e8eaf0; font-weight: 600; letter-spacing: .03em;">
                    <i class="fas fa-project-diagram mr-2" style="color: #7c8fad;"></i>
                    Struttura ingredienti &mdash; <span id="tree-product-name"></span>
                </h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Chiudi"
                        style="color: #7c8fad; opacity: 1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0" style="background: #f5f6f8;">
                <div id="tree-loading" class="text-center py-5">
                    <i class="fas fa-spinner fa-spin fa-2x" style="color: #7c8fad;"></i>
                    <p class="mt-2 text-muted small">Caricamento struttura…</p>
                </div>
                <div id="network-container" style="height: 540px; display: none; background: #f5f6f8;"></div>
            </div>
            <div class="modal-footer" style="background: #f5f6f8; border-top: 1px solid #dde0e7; padding: 10px 16px;">
                <small class="text-muted mr-auto">
                    <i class="fas fa-mouse-pointer mr-1"></i> Trascina per esplorare · scroll per zoom
                </small>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-dismiss="modal">
                    Chiudi
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://unpkg.com/vis-network@9.1.9/standalone/umd/vis-network.min.js"></script>
<script>
$(document).ready(function () {

    var csrfToken   = $('meta[name="csrf-token"]').attr('content');
    var networkInst = null;

    // ---- DataTable prodotti ---------------------------------------------
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
            headers: { 'X-CSRF-TOKEN': csrfToken },
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
                    return '<div class="form-check"><input class="form-check-input" type="checkbox"'
                         + ' name="selected[]" value="' + row.id + '"></div>';
                }
            },
            {
                targets: 4,
                render: function (data, type, row) {
                    var treeBtn = '';
                    if (row.has_recipe) {
                        treeBtn = '<button type="button" class="btn btn-secondary btn-sm btn_tree mr-1"'
                                + ' data-id="' + data + '"'
                                + ' data-name="' + $('<span>').text(row.name).html() + '"'
                                + ' title="Struttura ingredienti">'
                                + '<i class="fas fa-project-diagram"></i></button>';
                    }
                    return treeBtn
                         + '<button type="button" class="btn btn-primary btn-sm btn_edit"'
                         + ' data-id="' + data + '"><i class="fa fa-edit"></i></button>';
                }
            },
        ],
    });

    // ---- Modal grafo ingredienti ----------------------------------------

    $(document).on('click', '.btn_tree', function () {
        var productId   = $(this).data('id');
        var productName = $(this).data('name');

        $('#tree-product-name').text(productName);
        $('#tree-loading').show();
        $('#network-container').hide().empty();
        if (networkInst) { networkInst.destroy(); networkInst = null; }

        $('#modal-product-tree').modal('show');

        $.get('/products/' + productId + '/tree')
            .done(function (data) {
                $('#tree-loading').hide();
                if (!data.nodes || data.nodes.length === 0) {
                    $('#network-container')
                        .html('<div class="p-4 text-muted text-center">'
                            + '<i class="fas fa-info-circle mr-1"></i>'
                            + 'Nessun ingrediente configurato.</div>')
                        .show();
                    return;
                }
                $('#network-container').show();
                buildNetwork(data.nodes, data.edges);
            })
            .fail(function () {
                $('#tree-loading').hide();
                $('#network-container')
                    .html('<div class="alert alert-danger m-3">'
                        + 'Errore nel caricamento della struttura.</div>')
                    .show();
            });
    });

    $('#modal-product-tree').on('shown.bs.modal', function () {
        if (networkInst) networkInst.fit({ animation: { duration: 400, easingFunction: 'easeInOutQuad' } });
    });

    $('#modal-product-tree').on('hidden.bs.modal', function () {
        if (networkInst) { networkInst.destroy(); networkInst = null; }
        $('#network-container').empty().hide();
    });

    // ---- vis-network ----------------------------------------------------

    function buildNetwork(rawNodes, rawEdges) {

        /*
         * Palette professionale:
         *   radice  → rettangolo antracite scuro  #1e2532 / testo bianco
         *   livello 1 → rettangolo blu-grigio    #2c3e5c / testo bianco
         *   livelli profondi → rettangoli grigi  scala chiarente
         */
        var levelColors = [
            null,                          // placeholder (livello 0 = radice)
            { bg: '#2c3e5c', border: '#1e2a3e', font: '#ffffff' },  // livello 1
            { bg: '#3d5475', border: '#2c3e5c', font: '#ffffff' },  // livello 2
            { bg: '#546a8a', border: '#3d5475', font: '#ffffff' },  // livello 3
            { bg: '#6b809f', border: '#546a8a', font: '#ffffff' },  // livello 4
            { bg: '#8496ae', border: '#6b809f', font: '#ffffff' },  // livello 5+
        ];

        function colorForLevel(level) {
            if (level === 0) return { bg: '#1e2532', border: '#111622', font: '#ffffff' };
            var idx = Math.min(level, levelColors.length - 1);
            return levelColors[idx];
        }

        var visNodes = new vis.DataSet(rawNodes.map(function (n) {
            var c = colorForLevel(n.level || 0);
            return {
                id:     n.id,
                label:  n.label,
                level:  n.level || 0,
                shape:  'box',
                color: {
                    background: c.bg,
                    border:     c.border,
                    highlight:  { background: c.bg, border: '#f0ad4e' },
                    hover:      { background: c.bg, border: '#f0ad4e' },
                },
                font: {
                    color: c.font,
                    size:  n.root ? 14 : 13,
                    face:  'Inter, Segoe UI, sans-serif',
                    bold:  n.root ? { color: '#ffffff', size: 14 } : false,
                },
                widthConstraint: { minimum: 90, maximum: 180 },
                margin: { top: 8, right: 12, bottom: 8, left: 12 },
                borderWidth: 1,
                borderWidthSelected: 2,
                shadow: { enabled: true, color: 'rgba(0,0,0,.18)', size: 6, x: 2, y: 2 },
            };
        }));

        var visEdges = new vis.DataSet(rawEdges.map(function (e, i) {
            return {
                id:     i,
                from:   e.from,
                to:     e.to,
                label:  e.label || '',
                arrows: { to: { enabled: true, scaleFactor: 0.6 } },
                color:  { color: '#9aabb8', highlight: '#f0ad4e', hover: '#f0ad4e' },
                font: {
                    size:       11,
                    color:      '#4a5568',
                    face:       'Inter, Segoe UI, sans-serif',
                    align:      'middle',
                    background: '#f5f6f8',
                    strokeWidth: 0,
                },
                width:  1.5,
                smooth: { type: 'cubicBezier', forceDirection: 'vertical', roundness: 0.35 },
            };
        }));

        var container = document.getElementById('network-container');
        var options = {
            layout: {
                hierarchical: {
                    enabled:         true,
                    direction:       'UD',
                    sortMethod:      'directed',
                    levelSeparation: 100,
                    nodeSpacing:     130,
                    treeSpacing:     160,
                    blockShifting:   true,
                    edgeMinimization: true,
                    parentCentralization: true,
                },
            },
            physics: { enabled: false },
            interaction: {
                dragNodes:    true,
                zoomView:     true,
                dragView:     true,
                hover:        true,
                tooltipDelay: 150,
                keyboard:     false,
            },
            nodes: {
                borderRadius: 4,
            },
            edges: {
                selectionWidth: 2,
            },
        };

        networkInst = new vis.Network(container, { nodes: visNodes, edges: visEdges }, options);
        networkInst.fit({ animation: { duration: 500, easingFunction: 'easeInOutQuad' } });
    }

});
</script>
@stop
