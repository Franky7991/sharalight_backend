
<?php $__env->startSection('title', 'Modifica Prodotto'); ?>
<?php $__env->startSection('content_header'); ?><?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">

    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header pb-0">
                <h4 class="mb-0">Dati Prodotto</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo e(route('products.update', [$product->id])); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <?php if($errors->any()): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li><?php echo e($error); ?></li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="name">Nome *</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-box"></i></span>
                            </div>
                            <input type="text" id="name" name="name" value="<?php echo e($product->name); ?>"
                                class="form-control" placeholder="Nome" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="product_category_id">Categoria *</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-boxes"></i></span>
                            </div>
                            <select id="product_category_id" name="product_category_id" class="form-control" required>
                                <option value="">-- Seleziona --</option>
                                <?php $__currentLoopData = $productCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($cat->id); ?>" <?php echo e($product->product_category_id == $cat->id ? 'selected' : ''); ?>>
                                        <?php echo e($cat->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="type">Tipo *</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-tag"></i></span>
                            </div>
                            <select id="type" name="type" class="form-control" required>
                                <option value="">-- Seleziona --</option>
                                <?php $__currentLoopData = $productTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($value); ?>" <?php echo e($product->type === $value ? 'selected' : ''); ?>>
                                        <?php echo e($label); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-6">
                            <button type="submit" class="btn btn-primary btn-block btn-sm">
                                <i class="fa fa-save"></i> Salva
                            </button>
                        </div>
                        <div class="col-6">
                            <a href="<?php echo e(route('products.index')); ?>">
                                <button type="button" class="btn btn-danger btn-block btn-sm">
                                    <i class="fa fa-backward"></i> Indietro
                                </button>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header p-0">
                <ul class="nav nav-tabs" id="productTabs" role="tablist">
                    <?php if($product->hasRecipe()): ?>
                    <li class="nav-item">
                        <a class="nav-link active" id="tab-recipe" data-toggle="tab" href="#pane-recipe" role="tab">
                            <i class="fas fa-list-ul mr-1"></i> Ricetta
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="productTabsContent">
                    <?php if($product->hasRecipe()): ?>
                    <div class="tab-pane fade show active" id="pane-recipe" role="tabpanel">
                        <?php echo $__env->make('product.tabs.recipe', ['product' => $product, 'productCategories' => $productCategories], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>
                    <?php else: ?>
                    <div class="text-muted text-center py-4">
                        <i class="fas fa-info-circle mr-1"></i>
                        La tab Ricetta è disponibile solo per Semi Lavorati e Prodotti Finiti.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
<?php if($product->hasRecipe()): ?>
<style>
    /* Fix backdrop per modal annidate (Bootstrap 4) */
    .modal-backdrop + .modal-backdrop { z-index: 1055; }
    #modal-detail-pick { z-index: 1060; }
    #modal-detail-pick + .modal-backdrop { z-index: 1055; }
</style>
<?php echo $__env->make('product.tabs.recipe_js', ['product' => $product], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\project\shara_light\backend\resources\views\product\show.blade.php ENDPATH**/ ?>