
<?php $__env->startSection('title', 'Nuova Conversione'); ?>
<?php $__env->startSection('content_header'); ?><?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-header pb-0">
        <h4 class="mb-0">Nuova Conversione Unità di Misura</h4>
    </div>
    <div class="card-body">
        <form method="POST" action="<?php echo e(route('unit-conversions.store')); ?>">
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
                    <label for="from_unit_of_measure_id">Da Unità di Misura *</label>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-ruler"></i></span>
                        </div>
                        <select id="from_unit_of_measure_id" name="from_unit_of_measure_id" class="form-control" required>
                            <option value="">-- Seleziona --</option>
                            <?php $__currentLoopData = $unitOfMeasures; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $uom): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($uom->id); ?>" <?php echo e(old('from_unit_of_measure_id') == $uom->id ? 'selected' : ''); ?>>
                                    <?php echo e($uom->name); ?> (<?php echo e($uom->symbol); ?>)
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>

                <div class="col-6">
                    <label for="from_quantity">Quantità Da *</label>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                        </div>
                        <input type="text" id="from_quantity" name="from_quantity"
                            value="<?php echo e(old('from_quantity')); ?>"
                            class="form-control" placeholder="0,00" autocomplete="off" required>
                    </div>
                </div>

                
                <div class="col-6">
                    <label for="to_unit_of_measure_id">A Unità di Misura *</label>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-ruler"></i></span>
                        </div>
                        <select id="to_unit_of_measure_id" name="to_unit_of_measure_id" class="form-control" required>
                            <option value="">-- Seleziona --</option>
                            <?php $__currentLoopData = $unitOfMeasures; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $uom): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($uom->id); ?>" <?php echo e(old('to_unit_of_measure_id') == $uom->id ? 'selected' : ''); ?>>
                                    <?php echo e($uom->name); ?> (<?php echo e($uom->symbol); ?>)
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>

                <div class="col-6">
                    <label for="to_quantity">Quantità A *</label>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                        </div>
                        <input type="text" id="to_quantity" name="to_quantity"
                            value="<?php echo e(old('to_quantity')); ?>"
                            class="form-control" placeholder="0,00" autocomplete="off" required>
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
                            <a href="<?php echo e(route('unit-conversions.index')); ?>">
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

<?php $__env->startSection('js'); ?>
<script>
$(document).ready(function () {
    function formatIt(input) {
        $(input).on('blur', function () {
            var raw = $(this).val().trim().replace(/\./g, '').replace(',', '.');
            var n   = parseFloat(raw);
            if (!isNaN(n)) {
                $(this).val(n.toLocaleString('it-IT', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            }
        });
        $(input).on('keypress', function (e) {
            if (!/[\d,\.]/.test(String.fromCharCode(e.which))) e.preventDefault();
        });
    }
    formatIt('#from_quantity');
    formatIt('#to_quantity');
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\project\shara_light\backend\resources\views\unit_conversion\create.blade.php ENDPATH**/ ?>