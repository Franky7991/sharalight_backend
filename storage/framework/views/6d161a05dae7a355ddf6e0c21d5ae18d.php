
<?php $__env->startSection('title', 'Impostazioni'); ?>
<?php $__env->startSection('content_header'); ?><?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header pb-0">
                <h4 class="mb-0">Impostazioni</h4>
            </div>
            <div class="card-body">

                <?php if(session('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle mr-1"></i> <?php echo e(session('success')); ?>

                        <button type="button" class="close" data-dismiss="alert" aria-label="Chiudi">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo e(route('settings.update')); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>

                    
                    <div class="form-group">
                        <label for="<?php echo e(\App\Models\Setting::KEY_WAREHOUSE_LOAD_CAUSAL); ?>">
                            Causale <em>Carico in Magazzino</em>
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="fas fa-arrow-circle-down"></i>
                                </span>
                            </div>
                            <select
                                id="<?php echo e(\App\Models\Setting::KEY_WAREHOUSE_LOAD_CAUSAL); ?>"
                                name="<?php echo e(\App\Models\Setting::KEY_WAREHOUSE_LOAD_CAUSAL); ?>"
                                class="form-control <?php $__errorArgs = [\App\Models\Setting::KEY_WAREHOUSE_LOAD_CAUSAL];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <option value="">— Nessuna —</option>
                                <?php $__currentLoopData = $causals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $causal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($causal->id); ?>"
                                        <?php echo e(($settings[\App\Models\Setting::KEY_WAREHOUSE_LOAD_CAUSAL] ?? '') == $causal->id ? 'selected' : ''); ?>>
                                        <?php echo e($causal->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = [\App\Models\Setting::KEY_WAREHOUSE_LOAD_CAUSAL];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <small class="form-text text-muted">
                            Causale usata di default per i movimenti di carico in magazzino.
                        </small>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-6">
                            <button type="submit" class="btn btn-primary btn-block btn-sm">
                                <i class="fa fa-save mr-1"></i> Salva
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\project\shara_light\backend\resources\views/setting/index.blade.php ENDPATH**/ ?>