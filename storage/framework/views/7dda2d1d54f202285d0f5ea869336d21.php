<div <?php echo e($attributes->merge(['class' => $makeAlertClass()])); ?>>

    
    <?php if(isset($dismissable)): ?>
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">
            &times;
        </button>
    <?php endif; ?>

    
    <?php if(! empty($title) || ! empty($icon)): ?>
        <h5>
            <?php if(! empty($icon)): ?>
                <i class="icon <?php echo e($icon); ?>"></i>
            <?php endif; ?>

            <?php if(! empty($title)): ?>
                <?php echo e($title); ?>

            <?php endif; ?>
        </h5>
    <?php endif; ?>

    
    <?php echo e($slot); ?>


</div>
<?php /**PATH C:\project\shara_light\backend\vendor\jeroennoten\laravel-adminlte\resources\views\components\widget\alert.blade.php ENDPATH**/ ?>