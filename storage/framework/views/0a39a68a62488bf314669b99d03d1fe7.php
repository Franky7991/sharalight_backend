


<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0">Ingredienti / Componenti</h6>
    <button type="button" class="btn btn-primary btn-sm" id="btn-add-recipe">
        <i class="fa fa-plus"></i>
    </button>
</div>

<table id="table_recipe" class="table table-hover table-sm" width="100%">
    <thead>
        <tr>
            <th>Categoria Prodotto</th>
            <th class="text-right">Quantità</th>
            <th>U.M.</th>
            <th style="width:110px;">Azioni</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>


<div class="modal fade" id="modal-recipe" tabindex="-1" role="dialog" aria-labelledby="modal-recipe-label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-recipe-label">Aggiungi riga ricetta</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Chiudi">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="modal-recipe-errors" class="alert alert-danger d-none">
                    <ul class="mb-0" id="modal-recipe-errors-list"></ul>
                </div>
                <input type="hidden" id="recipe_id" value="">

                <div class="form-group">
                    <label for="recipe_product_category_id">Categoria Prodotto *</label>
                    <select id="recipe_product_category_id" class="form-control">
                        <option value="">-- Seleziona --</option>
                        <?php $__currentLoopData = $productCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($cat->id); ?>"
                                data-uom-id="<?php echo e($cat->unitOfMeasure?->id ?? ''); ?>"
                                data-uom-symbol="<?php echo e($cat->unitOfMeasure?->symbol ?? ''); ?>"
                                data-uom-name="<?php echo e($cat->unitOfMeasure?->name ?? ''); ?>">
                                <?php echo e($cat->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="form-group" id="uom-select-group" style="display:none;">
                    <label for="recipe_unit_of_measure_id">Unità di Misura *</label>
                    <select id="recipe_unit_of_measure_id" class="form-control">
                        <option value="">-- Seleziona --</option>
                        <?php $__currentLoopData = $unitOfMeasures; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $uom): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($uom->id); ?>" data-symbol="<?php echo e($uom->symbol); ?>">
                                <?php echo e($uom->name); ?> (<?php echo e($uom->symbol); ?>)
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="recipe_quantity">Quantità *</label>
                    <div class="input-group">
                        <input type="text" id="recipe_quantity" class="form-control"
                            placeholder="0,00" autocomplete="off">
                        <div class="input-group-append">
                            <span class="input-group-text" id="uom-symbol-addon">—</span>
                        </div>
                    </div>
                    <small class="form-text text-muted">Usa la virgola come separatore decimale (es. 1.250,50)</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fa fa-times"></i> Annulla
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="btn-save-recipe">
                    <i class="fa fa-save"></i> Salva
                </button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="modal-recipe-detail" tabindex="-1" role="dialog"
     aria-labelledby="modal-recipe-detail-label" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-recipe-detail-label">
                    Prodotti — <span id="detail-category-name"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Chiudi">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="detail-recipe-id" value="">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span></span>
                    <button type="button" class="btn btn-primary btn-sm" id="btn-add-detail">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>

                <table id="table_detail_selected" class="table table-sm table-hover" width="100%">
                    <thead>
                        <tr>
                            <th>Prodotto</th>
                            <th style="width:60px;">Azioni</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fa fa-times"></i> Chiudi
                </button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="modal-detail-pick" tabindex="-1" role="dialog"
     aria-labelledby="modal-detail-pick-label" aria-hidden="true"
     style="z-index:1060;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-detail-pick-label">Aggiungi prodotto</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Chiudi">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table id="table_detail_available" class="table table-sm table-hover" width="100%">
                    <thead>
                        <tr>
                            <th>Prodotto</th>
                            <th style="width:60px;">Aggiungi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fa fa-times"></i> Chiudi
                </button>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\project\shara_light\backend\resources\views\product\tabs\recipe.blade.php ENDPATH**/ ?>