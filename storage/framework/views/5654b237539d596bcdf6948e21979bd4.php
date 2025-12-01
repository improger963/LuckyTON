<?php if(session('success')): ?>
<div class="admin-alert-success mb-6">
    <div class="flex items-center">
        <i class="bi bi-check-circle mr-2"></i>
        <span><?php echo e(session('success')); ?></span>
    </div>
    <button type="button" class="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300"
            onclick="this.parentElement.remove()">
        <i class="bi bi-x-lg"></i>
    </button>
</div>
<?php endif; ?>

<?php if(session('error')): ?>
<div class="admin-alert-error mb-6">
    <div class="flex items-center">
        <i class="bi bi-exclamation-circle mr-2"></i>
        <span><?php echo e(session('error')); ?></span>
    </div>
    <button type="button" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300"
            onclick="this.parentElement.remove()">
        <i class="bi bi-x-lg"></i>
    </button>
</div>
<?php endif; ?>

<?php if($errors->any()): ?>
<div class="admin-alert-error mb-6">
    <div>
        <div class="flex items-center">
            <i class="bi bi-exclamation-circle mr-2"></i>
            <span>There were some errors with your request:</span>
        </div>
        <ul class="list-disc list-inside mt-2 text-sm">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
    <button type="button" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300"
            onclick="this.parentElement.remove()">
        <i class="bi bi-x-lg"></i>
    </button>
</div>
<?php endif; ?><?php /**PATH /var/www/html/resources/views/admin/layouts/partials/notifications.blade.php ENDPATH**/ ?>