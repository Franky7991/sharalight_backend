
<?php $__env->startSection('title', 'Movimenti'); ?>
<?php $__env->startSection('content_header'); ?><?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-header pb-0">
        <div class="d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Movimenti</h4>
            <button type="button" class="btn btn-success btn-sm" id="btn-load-warehouse">
                <i class="fas fa-arrow-circle-down mr-1"></i> Carico in Magazzino
            </button>
        </div>
    </div>
    <div class="card-body">
        <table id="table_movements" class="table table-hover" width="100%">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Magazzino</th>
                    <th>Prodotto</th>
                    <th>Causale</th>
                    <th>Tipo</th>
                    <th class="text-right">Quantità</th>
                    <th>U.M.</th>
                    <th style="width:60px;">Azioni</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>


<div class="modal fade" id="modal-load" tabindex="-1" role="dialog"
     aria-labelledby="modal-load-label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-load-label">
                    <i class="fas fa-arrow-circle-down mr-1 text-success"></i>
                    Carico in Magazzino
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Chiudi">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <div id="modal-load-errors" class="alert alert-danger d-none">
                    <ul class="mb-0" id="modal-load-errors-list"></ul>
                </div>

                
                <input type="hidden" id="load_causal_id"          value="<?php echo e($defaultLoadCausalId ?? ''); ?>">
                <input type="hidden" id="load_unit_of_measure_id" value="">

                
                <div class="form-group">
                    <label for="load_warehouse_id">Magazzino <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-warehouse"></i></span>
                        </div>
                        <select id="load_warehouse_id" class="form-control">
                            <option value="">— Seleziona —</option>
                            <?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wh): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($wh->id); ?>"><?php echo e($wh->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>

                
                <div class="form-group">
                    <label for="load_product_id">Prodotto <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-box"></i></span>
                        </div>
                        <select id="load_product_id" class="form-control">
                            <option value="">— Seleziona —</option>
                            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($p->id); ?>"
                                    data-uom-id="<?php echo e($p->productCategory?->unitOfMeasure?->id ?? ''); ?>"
                                    data-uom-symbol="<?php echo e($p->productCategory?->unitOfMeasure?->symbol ?? ''); ?>">
                                    <?php echo e($p->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>

                
                <div class="form-group">
                    <label for="load_qnt">Quantità <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" id="load_qnt" class="form-control"
                               placeholder="0,00" autocomplete="off">
                        <div class="input-group-append">
                            <span class="input-group-text" id="load-uom-addon"
                                  style="min-width:60px; justify-content:center;">
                                —
                            </span>
                        </div>
                    </div>
                    <small class="form-text text-muted">
                        L'unità di misura è determinata dalla categoria del prodotto.
                    </small>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fa fa-times"></i> Annulla
                </button>
                <button type="button" class="btn btn-success btn-sm" id="btn-save-load">
                    <i class="fas fa-arrow-circle-down mr-1"></i> Registra Carico
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

    // ---- Formato italiano -----------------------------------------------
    function formatIt(val, dec) {
        dec = dec === undefined ? 2 : dec;
        var n = parseFloat(val);
        if (isNaN(n)) return '';
        return n.toLocaleString('it-IT', { minimumFractionDigits: dec, maximumFractionDigits: dec });
    }
    function parseIt(str) {
        if (!str) return NaN;
        return parseFloat(str.replace(/\./g, '').replace(',', '.'));
    }

    // ---- DataTable movimenti --------------------------------------------
    var movTable = $('#table_movements').DataTable({
        order: [[0, 'desc']],
        pageLength: 25,
        ajax: {
            type: 'POST',
            url: '<?php echo e(route('movements.datatable')); ?>',
            headers: { 'X-CSRF-TOKEN': csrfToken },
        },
        columns: [
            { data: 'created_at',           name: 'created_at' },
            { data: 'warehouse_name',        name: 'warehouse_name' },
            { data: 'product_name',          name: 'product_name' },
            { data: 'causal_name',           name: 'causal_name' },
            { data: 'causal_type_label',     name: 'causal_type_label', orderable: false },
            { data: 'qnt',                   name: 'qnt', className: 'text-right' },
            { data: 'unit_of_measure_symbol',name: 'unit_of_measure_symbol', orderable: false },
            { data: 'id',                    name: 'id', orderable: false, searchable: false },
        ],
        columnDefs: [
            {
                targets: 0,
                render: function (data) {
                    if (!data) return '-';
                    var d = new Date(data);
                    return d.toLocaleDateString('it-IT') + ' ' + d.toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' });
                }
            },
            {
                targets: 5,
                render: function (data) { return formatIt(data, 2); }
            },
            {
                targets: 7,
                render: function (id) {
                    return '<button class="btn btn-danger btn-xs btn-delete-movement" data-id="' + id + '" title="Elimina">'
                         + '<i class="fa fa-trash"></i></button>';
                }
            },
        ],
    });

    // Elimina movimento
    $('#table_movements').on('click', '.btn-delete-movement', function () {
        if (!confirm('Eliminare questo movimento?')) return;
        var id = $(this).data('id');
        $.ajax({
            url:     '/movements/' + id,
            type:    'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function () { movTable.ajax.reload(null, false); },
            error:   function () { alert('Errore durante l\'eliminazione.'); },
        });
    });

    // ---- Modal Carico in Magazzino --------------------------------------

    $('#btn-load-warehouse').on('click', function () {
        resetLoadModal();
        $('#modal-load').modal('show');
    });

    // Al cambio prodotto: aggiorna UdM addon
    $('#load_product_id').on('change', function () {
        var opt    = $(this).find('option:selected');
        var uomId  = opt.data('uom-id')     || '';
        var uomSym = opt.data('uom-symbol') || '—';
        $('#load_unit_of_measure_id').val(uomId);
        $('#load-uom-addon').text(uomSym);
    });

    // Formato quantità
    $('#load_qnt').on('blur', function () {
        var n = parseIt($(this).val().trim());
        if (!isNaN(n)) $(this).val(formatIt(n, 2));
    });
    $('#load_qnt').on('keypress', function (e) {
        if (!/[\d,\.]/.test(String.fromCharCode(e.which))) e.preventDefault();
    });

    // Salva
    $('#btn-save-load').on('click', function () {
        hideErrors();

        var causalId = $('#load_causal_id').val();
        if (!causalId) {
            showErrors({ causal_id: ['Nessuna causale di carico configurata. Impostala in Impostazioni.'] });
            return;
        }

        $.ajax({
            url:     '<?php echo e(route('movements.store')); ?>',
            type:    'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: {
                warehouse_id:       $('#load_warehouse_id').val(),
                product_id:         $('#load_product_id').val(),
                causal_id:          causalId,
                qnt:                $('#load_qnt').val().trim(),
                unit_of_measure_id: $('#load_unit_of_measure_id').val(),
            },
            success: function () {
                $('#modal-load').modal('hide');
                movTable.ajax.reload(null, false);
            },
            error: function (xhr) {
                if (xhr.status === 422) showErrors(xhr.responseJSON.errors);
                else alert('Errore durante il salvataggio.');
            },
        });
    });

    function resetLoadModal() {
        $('#load_warehouse_id').val('');
        $('#load_product_id').val('');
        $('#load_qnt').val('');
        $('#load_unit_of_measure_id').val('');
        $('#load-uom-addon').text('—');
        hideErrors();
    }

    function showErrors(errors) {
        var list = $('#modal-load-errors-list').empty();
        $.each(errors, function (f, msgs) {
            $.each(msgs, function (i, msg) { list.append('<li>' + msg + '</li>'); });
        });
        $('#modal-load-errors').removeClass('d-none');
    }

    function hideErrors() {
        $('#modal-load-errors').addClass('d-none');
        $('#modal-load-errors-list').empty();
    }

});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\project\shara_light\backend\resources\views\movement\index.blade.php ENDPATH**/ ?>