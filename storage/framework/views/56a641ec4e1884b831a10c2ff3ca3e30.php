
<?php $__env->startSection('title', 'Nuova Unità di Misura'); ?>
<?php $__env->startSection('content_header'); ?><?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-header pb-0">
        <h4 class="mb-0">Nuova Unità di Misura</h4>
    </div>
    <div class="card-body">
        <form method="POST" action="<?php echo e(route('unit-of-measures.store')); ?>">
            <?php echo csrf_field(); ?>
            <?php if($errors->any()): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
                </div>
            <?php endif; ?>
            <div class="row">
                <div class="col-6">
                    <label for="name">Nome *</label>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-tag"></i></span></div>
                        <input type="text" id="name" name="name" value="<?php echo e(old('name')); ?>" class="form-control" placeholder="Nome" required>
                    </div>
                </div>
                <div class="col-6">
                    <label for="symbol">Simbolo *</label>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-font"></i></span></div>
                        <input type="text" id="symbol" name="symbol" value="<?php echo e(old('symbol')); ?>" class="form-control" placeholder="Simbolo" required>
                    </div>
                </div>
                <div class="col-12">
                    <div class="row">
                        <div class="col-3">
                            <button type="submit" class="btn btn-primary btn-block btn-sm"><i class="fa fa-save"></i> Salva</button>
                        </div>
                        <div class="col-3">
                            <a href="<?php echo e(route('unit-of-measures.index')); ?>">
                                <button type="button" class="btn btn-danger btn-block btn-sm"><i class="fa fa-backward"></i> Indietro</button>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\project\shara_light\backend\resources\views\unit_of_measure\create.blade.php ENDPATH**/ ?>