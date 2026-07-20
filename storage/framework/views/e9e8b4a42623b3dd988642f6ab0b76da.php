<script>
(function () {
    var productId    = <?php echo e($product->id); ?>;
    var urlDatatable = "<?php echo e(route('recipes.datatable', $product->id)); ?>";
    var urlStore     = "<?php echo e(route('recipes.store')); ?>";
    var urlUpdate    = function (id) { return '/recipes/' + id; };
    var urlDestroy   = function (id) { return '/recipes/' + id; };
    var csrfToken    = $('meta[name="csrf-token"]').attr('content');

    // ---- Utilità formato italiano ----------------------------------------

    /**
     * Numero JS → stringa italiana: 1234.5 → "1.234,50"
     */
    function formatIt(value, decimals) {
        decimals = (decimals === undefined) ? 2 : decimals;
        var n = parseFloat(value);
        if (isNaN(n)) return '';
        return n.toLocaleString('it-IT', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals,
        });
    }

    /**
     * Stringa italiana → float: "1.234,56" → 1234.56
     */
    function parseIt(str) {
        if (!str) return NaN;
        // rimuovi i punti delle migliaia, sostituisci virgola con punto
        return parseFloat(str.replace(/\./g, '').replace(',', '.'));
    }

    // ---- DataTable -------------------------------------------------------

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
                // Quantità → formato italiano
                targets: 1,
                render: function (data) {
                    return formatIt(data, 2);
                }
            },
            {
                // Azioni
                targets: 3,
                render: function (id, type, row) {
                    return '<button class="btn btn-primary btn-xs btn-edit-recipe mr-1"'
                         + ' data-id="' + id + '"'
                         + ' data-category="' + row.product_category_id + '"'
                         + ' data-uom-symbol="' + (row.unit_of_measure_symbol || '') + '"'
                         + ' data-qty="' + row.quantity + '">'
                         + '<i class="fa fa-edit"></i></button>'
                         + '<button class="btn btn-danger btn-xs btn-delete-recipe" data-id="' + id + '">'
                         + '<i class="fa fa-trash"></i></button>';
                }
            },
        ],
    });

    // ---- Simbolo UdM nell'addon del campo quantità ----------------------

    function updateUomAddon(symbol) {
        $('#uom-symbol-addon').text(symbol || '—');
    }

    $('#recipe_product_category_id').on('change', function () {
        var opt = $(this).find('option:selected');
        updateUomAddon(opt.data('uom-symbol'));
    });

    // ---- Formattazione live sul campo quantità ---------------------------

    $('#recipe_quantity').on('blur', function () {
        var raw = $(this).val().trim();
        if (!raw) return;
        var n = parseIt(raw);
        if (!isNaN(n)) {
            $(this).val(formatIt(n, 2));
        }
    });

    // Consenti solo cifre, virgola, punto (separatore migliaia)
    $('#recipe_quantity').on('keypress', function (e) {
        var allowed = /[\d,\.]/;
        if (!allowed.test(String.fromCharCode(e.which))) {
            e.preventDefault();
        }
    });

    // ---- Modal: nuovo ----

    $('#btn-add-recipe').on('click', function () {
        $('#modal-recipe-label').text('Aggiungi riga ricetta');
        $('#recipe_id').val('');
        $('#recipe_product_category_id').val('');
        $('#recipe_quantity').val('');
        updateUomAddon('');
        hideErrors();
        $('#modal-recipe').modal('show');
    });

    // ---- Modal: modifica ----

    $('#table_recipe').on('click', '.btn-edit-recipe', function () {
        var btn = $(this);
        $('#modal-recipe-label').text('Modifica riga ricetta');
        $('#recipe_id').val(btn.data('id'));
        $('#recipe_product_category_id').val(btn.data('category'));
        $('#recipe_quantity').val(formatIt(btn.data('qty'), 2));
        updateUomAddon(btn.data('uom-symbol'));
        hideErrors();
        $('#modal-recipe').modal('show');
    });

    // ---- Elimina ----

    $('#table_recipe').on('click', '.btn-delete-recipe', function () {
        var id = $(this).data('id');
        if (!confirm('Eliminare questa riga?')) return;
        $.ajax({
            url:  urlDestroy(id),
            type: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function () { recipeTable.ajax.reload(null, false); },
            error: function () { alert('Errore durante l\'eliminazione.'); },
        });
    });

    // ---- Salva ----

    $('#btn-save-recipe').on('click', function () {
        var id         = $('#recipe_id').val();
        var categoryId = $('#recipe_product_category_id').val();
        var qtyRaw     = $('#recipe_quantity').val().trim();

        // Invia sempre con punto decimale al server
        var qtyForServer = qtyRaw.replace(/\./g, '').replace(',', '.');

        hideErrors();

        $.ajax({
            url:  id ? urlUpdate(id) : urlStore,
            type: id ? 'PUT' : 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: {
                product_id:          productId,
                product_category_id: categoryId,
                quantity:            qtyRaw, // il controller gestisce sia virgola che punto
            },
            success: function () {
                $('#modal-recipe').modal('hide');
                recipeTable.ajax.reload(null, false);
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    showErrors(xhr.responseJSON.errors);
                } else {
                    alert('Errore durante il salvataggio.');
                }
            },
        });
    });

    // ---- Helpers ----

    function showErrors(errors) {
        var list = $('#modal-recipe-errors-list');
        list.empty();
        $.each(errors, function (field, messages) {
            $.each(messages, function (i, msg) {
                list.append('<li>' + msg + '</li>');
            });
        });
        $('#modal-recipe-errors').removeClass('d-none');
    }

    function hideErrors() {
        $('#modal-recipe-errors').addClass('d-none');
        $('#modal-recipe-errors-list').empty();
    }
})();
</script>
<?php /**PATH C:\project\shara_light\backend\resources\views\product\tabs\recipe_js.blade.php ENDPATH**/ ?>