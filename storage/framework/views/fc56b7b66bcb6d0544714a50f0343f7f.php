

<?php $__env->startSection('title', 'Nuovo Utente'); ?>

<?php $__env->startSection('content_header'); ?><?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-header pb-0">
        <h4 class="mb-0">Nuovo Utente</h4>
    </div>
    <div class="card-body">
        <form method="POST" action="<?php echo e(route('users.store')); ?>">
            <?php echo csrf_field(); ?>
            <?php if($errors->any()): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>
            <div class="row">
                <div class="col-6">
                    <label for="name">Nome *</label>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                        </div>
                        <input type="text" id="name" name="name" value="<?php echo e(old('name')); ?>"
                            class="form-control" placeholder="Nome" required>
                    </div>
                </div>
                <div class="col-6">
                    <label for="email">Email *</label>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        </div>
                        <input type="email" id="email" name="email" value="<?php echo e(old('email')); ?>"
                            class="form-control" placeholder="Email" required>
                    </div>
                </div>
                <div class="col-6">
                    <label for="password">Password *</label>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                        </div>
                        <input type="password" id="password" name="password"
                            class="form-control" placeholder="Password" required>
                    </div>
                </div>
                <div class="col-12">
                    <div class="row">
                        <div class="col-3">
                            <button type="submit" class="btn btn-primary btn-block btn-sm">
                                <i class="fa fa-save"></i> Salva
                            </button>
                        </div>
                        <div class="col-3">
                            <a href="<?php echo e(route('users.index')); ?>">
                                <button type="button" class="btn btn-danger btn-block btn-sm">
                                    <i class="fa fa-backward"></i> Indietro
                                </button>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\project\shara_light\backend\resources\views\user\create.blade.php ENDPATH**/ ?>