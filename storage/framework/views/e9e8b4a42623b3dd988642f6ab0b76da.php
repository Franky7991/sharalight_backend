<script>
(function () {
    var productId          = <?php echo e($product->id); ?>;
    var urlDatatable       = "<?php echo e(route('recipes.datatable', $product->id)); ?>";
    var urlStore           = "<?php echo e(route('recipes.store')); ?>";
    var urlUpdate          = function (id) { return '/recipes/'                      + id; };
    var urlDestroy         = function (id) { return '/recipes/'                      + id; };
    var urlDetailList      = function (id) { return '/recipe-details/list/details/'  + id; };
    var urlDetailProducts  = function (id) { return '/recipe-details/list/products/' + id; };
    var urlDetailStore     = "<?php echo e(route('recipe-details.store')); ?>";
    var urlDetailDestroy   = function (id) { return '/recipe-details/'               + id; };
    var csrfToken          = $('meta[name="csrf-token"]').attr('content');

    // ---- Formato italiano -----------------------------------------------

    function formatIt(value, decimals) {
        decimals = decimals === undefined ? 2 : decimals;
        var n = parseFloat(value);
        if (isNaN(n)) return '';
        return n.toLocaleString('it-IT', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
    }

    function parseIt(str) {
        if (!str) return NaN;
        return parseFloat(str.replace(/\./g, '').replace(',', '.'));
    }

    // ====================================================================
    // DataTable principale: righe ricetta
    // ====================================================================

    var recipeTable = $('#table_recipe').DataTable({
        order: [[0, 'asc']],
        pageLength: -1,
        searching: false,
        lengthChange: false,
        info: false,
        ajax: {
            type: 'POST',
            url: urlDatatable,
            headers: { 'X-CSRF-TOKEN': csrfToken },
        },
        columns: [
            { data: 'product_category_name',  name: 'product_category_name' },
            { data: 'quantity',               name: 'quantity', className: 'text-right' },
            { data: 'unit_of_measure_symbol', name: 'unit_of_measure_symbol', orderable: false },
            { data: 'id',                     name: 'id', orderable: false, searchable: false },
        ],
        columnDefs: [
            {
                targets: 1,
                render: function (data) { return formatIt(data, 2); }
            },
            {
                targets: 2,
                render: function (data, type, row) {
                    if (!data || data === '-') return '-';
                    var name = row.unit_of_measure_name || '';
                    return name ? data + ' (' + name + ')' : data;
                }
            },
            {
                targets: 3,
                render: function (id, type, row) {
                    var count       = row.details_count || 0;
                    var btnClass    = count > 0 ? 'btn-success' : 'btn-info';
                    var badgeHtml   = count > 0
                        ? ' <span class="badge badge-light">' + count + '</span>'
                        : '';
                    return '<button class="btn ' + btnClass + ' btn-xs btn-detail-recipe mr-1"'
                         + ' data-id="' + id + '"'
                         + ' data-category-name="' + row.product_category_name + '"'
                         + ' title="Prodotti">'
                         + '<i class="fa fa-list"></i>' + badgeHtml + '</button>'
                         + '<button class="btn btn-primary btn-xs btn-edit-recipe mr-1"'
                         + ' data-id="' + id + '"'
                         + ' data-category="' + row.product_category_id + '"'
                         + ' data-uom-id="' + (row.unit_of_measure_id || '') + '"'
                         + ' data-uom-symbol="' + (row.unit_of_measure_symbol || '') + '"'
                         + ' data-qty="' + row.quantity + '"'
                         + ' title="Modifica">'
                         + '<i class="fa fa-edit"></i></button>'
                         + '<button class="btn btn-danger btn-xs btn-delete-recipe"'
                         + ' data-id="' + id + '"'
                         + ' title="Elimina">'
                         + '<i class="fa fa-trash"></i></button>';
                }
            },
        ],
    });

    // ====================================================================
    // Modal 1: aggiungi / modifica riga ricetta
    // ====================================================================

    function updateUomAddon(symbol) {
        $('#uom-symbol-addon').text(symbol || '—');
    }

    // Al cambio categoria: preseleziona la UdM della categoria e mostra la select
    $('#recipe_product_category_id').on('change', function () {
        var opt       = $(this).find('option:selected');
        var uomId     = opt.data('uom-id');
        var uomSymbol = opt.data('uom-symbol');

        if (opt.val()) {
            // Preseleziona la UdM della categoria
            $('#recipe_unit_of_measure_id').val(uomId || '');
            updateUomAddon(uomSymbol);
            $('#uom-select-group').show();
        } else {
            $('#recipe_unit_of_measure_id').val('');
            updateUomAddon('');
            $('#uom-select-group').hide();
        }
    });

    // Aggiorna il simbolo nell'addon quando si cambia manualmente la UdM
    $('#recipe_unit_of_measure_id').on('change', function () {
        var symbol = $(this).find('option:selected').data('symbol');
        updateUomAddon(symbol);
    });

    $('#recipe_quantity').on('blur', function () {
        var n = parseIt($(this).val().trim());
        if (!isNaN(n)) $(this).val(formatIt(n, 2));
    });

    $('#recipe_quantity').on('keypress', function (e) {
        if (!/[\d,\.]/.test(String.fromCharCode(e.which))) e.preventDefault();
    });

    $('#btn-add-recipe').on('click', function () {
        $('#modal-recipe-label').text('Aggiungi riga ricetta');
        $('#recipe_id, #recipe_quantity').val('');
        $('#recipe_product_category_id').val('');
        $('#recipe_unit_of_measure_id').val('');
        $('#uom-select-group').hide();
        updateUomAddon('');
        hideRecipeErrors();
        $('#modal-recipe').modal('show');
    });

    $('#table_recipe').on('click', '.btn-edit-recipe', function () {
        var btn = $(this);
        $('#modal-recipe-label').text('Modifica riga ricetta');
        $('#recipe_id').val(btn.data('id'));
        $('#recipe_product_category_id').val(btn.data('category'));
        $('#recipe_unit_of_measure_id').val(btn.data('uom-id'));
        $('#recipe_quantity').val(formatIt(btn.data('qty'), 2));
        updateUomAddon(btn.data('uom-symbol'));
        $('#uom-select-group').show();
        hideRecipeErrors();
        $('#modal-recipe').modal('show');
    });

    $('#table_recipe').on('click', '.btn-delete-recipe', function () {
        if (!confirm('Eliminare questa riga?')) return;
        $.ajax({
            url: urlDestroy($(this).data('id')), type: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function () { recipeTable.ajax.reload(null, false); },
            error:   function () { alert('Errore durante l\'eliminazione.'); },
        });
    });

    $('#btn-save-recipe').on('click', function () {
        var id = $('#recipe_id').val();
        hideRecipeErrors();
        $.ajax({
            url:  id ? urlUpdate(id) : urlStore,
            type: id ? 'PUT' : 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: {
                product_id:          productId,
                product_category_id: $('#recipe_product_category_id').val(),
                unit_of_measure_id:  $('#recipe_unit_of_measure_id').val(),
                quantity:            $('#recipe_quantity').val().trim(),
            },
            success: function () {
                $('#modal-recipe').modal('hide');
                recipeTable.ajax.reload(null, false);
            },
            error: function (xhr) {
                if (xhr.status === 422) showRecipeErrors(xhr.responseJSON.errors);
                else alert('Errore durante il salvataggio.');
            },
        });
    });

    function showRecipeErrors(errors) {
        var list = $('#modal-recipe-errors-list').empty();
        $.each(errors, function (f, msgs) {
            $.each(msgs, function (i, msg) { list.append('<li>' + msg + '</li>'); });
        });
        $('#modal-recipe-errors').removeClass('d-none');
    }
    function hideRecipeErrors() {
        $('#modal-recipe-errors').addClass('d-none');
        $('#modal-recipe-errors-list').empty();
    }

    // ====================================================================
    // Modal 2: dettagli prodotto della riga (DataTable + pulsante +)
    // ====================================================================

    var detailSelectedTable = null;

    $('#table_recipe').on('click', '.btn-detail-recipe', function () {
        var btn = $(this);
        $('#detail-recipe-id').val(btn.data('id'));
        $('#detail-category-name').text(btn.data('category-name'));
        $('#modal-recipe-detail').modal('show');
    });

    $('#modal-recipe-detail').on('shown.bs.modal', function () {
        var recipeId = $('#detail-recipe-id').val();
        if (detailSelectedTable) { detailSelectedTable.destroy(); detailSelectedTable = null; }
        detailSelectedTable = $('#table_detail_selected').DataTable({
            pageLength: -1,
            searching: false,
            lengthChange: false,
            info: false,
            order: [[0, 'asc']],
            ajax: {
                type: 'POST',
                url: urlDetailList(recipeId),
                headers: { 'X-CSRF-TOKEN': csrfToken },
            },
            columns: [
                { data: 'product_name', name: 'product_name' },
                { data: 'id',           name: 'id', orderable: false, searchable: false },
            ],
            columnDefs: [{
                targets: 1,
                render: function (id) {
                    return '<button class="btn btn-danger btn-xs btn-remove-detail" data-id="' + id + '">'
                         + '<i class="fa fa-trash"></i></button>';
                }
            }],
        });
    });

    $('#modal-recipe-detail').on('hidden.bs.modal', function () {
        if (detailSelectedTable) { detailSelectedTable.destroy(); detailSelectedTable = null; }
    });

    // Rimuovi prodotto
    $('#modal-recipe-detail').on('click', '.btn-remove-detail', function () {
        if (!confirm('Rimuovere questo prodotto?')) return;
        $.ajax({
            url: urlDetailDestroy($(this).data('id')), type: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function () {
                if (detailSelectedTable) detailSelectedTable.ajax.reload(null, false);
                recipeTable.ajax.reload(null, false);
            },
            error:   function () { alert('Errore durante la rimozione.'); },
        });
    });

    // ====================================================================
    // Modal 3: selezione prodotto disponibile (aperta dal + nella modal 2)
    // ====================================================================

    var detailAvailableTable = null;

    // Apri modal 3 dal pulsante + della modal 2
    $('#btn-add-detail').on('click', function () {
        $('#modal-detail-pick').modal('show');
    });

    $('#modal-detail-pick').on('shown.bs.modal', function () {
        var recipeId = $('#detail-recipe-id').val();
        if (detailAvailableTable) { detailAvailableTable.destroy(); detailAvailableTable = null; }
        detailAvailableTable = $('#table_detail_available').DataTable({
            pageLength: -1,
            searching: true,
            lengthChange: false,
            info: false,
            order: [[0, 'asc']],
            ajax: {
                type: 'POST',
                url: urlDetailProducts(recipeId),
                headers: { 'X-CSRF-TOKEN': csrfToken },
            },
            columns: [
                { data: 'name', name: 'name' },
                { data: 'id',   name: 'id', orderable: false, searchable: false },
            ],
            columnDefs: [{
                targets: 1,
                render: function (id) {
                    return '<button class="btn btn-primary btn-xs btn-pick-product" data-id="' + id + '">'
                         + '<i class="fa fa-plus"></i></button>';
                }
            }],
        });
    });

    $('#modal-detail-pick').on('hidden.bs.modal', function () {
        if (detailAvailableTable) { detailAvailableTable.destroy(); detailAvailableTable = null; }
    });

    // Aggiungi prodotto: chiude modal 3, aggiorna modal 2
    $('#modal-detail-pick').on('click', '.btn-pick-product', function () {
        var pickedProductId = $(this).data('id');
        var recipeId        = $('#detail-recipe-id').val();

        $.ajax({
            url:  urlDetailStore,
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: { recipe_id: recipeId, product_id: pickedProductId },
            success: function () {
                $('#modal-detail-pick').modal('hide');
                if (detailSelectedTable) detailSelectedTable.ajax.reload(null, false);
                recipeTable.ajax.reload(null, false);
            },
            error: function (xhr) {
                var msg = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message : 'Errore durante l\'inserimento.';
                alert(msg);
            },
        });
    });

})();
</script>
<?php /**PATH C:\project\shara_light\backend\resources\views\product\tabs\recipe_js.blade.php ENDPATH**/ ?>