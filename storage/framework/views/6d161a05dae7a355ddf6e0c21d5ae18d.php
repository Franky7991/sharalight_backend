
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

                    
                    <h6 class="text-uppercase text-muted font-weight-bold mb-3" style="font-size:.7rem; letter-spacing:.08em;">
                        <i class="fas fa-warehouse mr-1"></i> Magazzino
                    </h6>

                    <?php $key = \App\Models\Setting::KEY_WAREHOUSE_LOAD_CAUSAL; ?>
                    <div class="form-group">
                        <label for="<?php echo e($key); ?>">Causale <em>Carico in Magazzino</em></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-arrow-circle-down text-success"></i></span>
                            </div>
                            <select id="<?php echo e($key); ?>" name="<?php echo e($key); ?>"
                                class="form-control <?php $__errorArgs = [$key];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <option value="">— Nessuna —</option>
                                <?php $__currentLoopData = $loadCausals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($c->id); ?>" <?php echo e(($settings[$key] ?? '') == $c->id ? 'selected' : ''); ?>>
                                        <?php echo e($c->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = [$key];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <small class="form-text text-muted">Causale di default per i carichi manuali in magazzino.</small>
                    </div>

                    <hr>

                    
                    <h6 class="text-uppercase text-muted font-weight-bold mb-3" style="font-size:.7rem; letter-spacing:.08em;">
                        <i class="fas fa-industry mr-1"></i> Produzione
                    </h6>

                    <?php $key = \App\Models\Setting::KEY_PRODUCTION_UNLOAD_CAUSAL; ?>
                    <div class="form-group">
                        <label for="<?php echo e($key); ?>">Causale <em>Scarico per Produzione</em></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-arrow-circle-up text-danger"></i></span>
                            </div>
                            <select id="<?php echo e($key); ?>" name="<?php echo e($key); ?>"
                                class="form-control <?php $__errorArgs = [$key];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <option value="">— Nessuna —</option>
                                <?php $__currentLoopData = $unloadCausals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($c->id); ?>" <?php echo e(($settings[$key] ?? '') == $c->id ? 'selected' : ''); ?>>
                                        <?php echo e($c->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = [$key];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <small class="form-text text-muted">Causale usata per scaricare le materie prime durante la produzione.</small>
                    </div>

                    <?php $key = \App\Models\Setting::KEY_PRODUCTION_LOAD_CAUSAL; ?>
                    <div class="form-group">
                        <label for="<?php echo e($key); ?>">Causale <em>Carico per Produzione</em></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-arrow-circle-down text-success"></i></span>
                            </div>
                            <select id="<?php echo e($key); ?>" name="<?php echo e($key); ?>"
                                class="form-control <?php $__errorArgs = [$key];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <option value="">— Nessuna —</option>
                                <?php $__currentLoopData = $loadCausals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($c->id); ?>" <?php echo e(($settings[$key] ?? '') == $c->id ? 'selected' : ''); ?>>
                                        <?php echo e($c->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = [$key];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <small class="form-text text-muted">Causale usata per caricare il prodotto finito/semi-lavorato dopo la produzione.</small>
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