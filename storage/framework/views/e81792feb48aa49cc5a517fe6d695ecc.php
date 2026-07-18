

<?php $__env->startSection('title', 'Home'); ?>

<?php $__env->startSection('content_header'); ?>
    <h1>Dashboard</h1>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <p class="mb-0">Benvenuto, <strong><?php echo e(Auth::user()->name); ?></strong>!</p>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\project\shara_light\backend\resources\views\home.blade.php ENDPATH**/ ?>