<div class="flex items-center justify-between">
    <div class="flex items-center">
        <div class="admin-user-avatar">
            A
        </div>
        <div class="ml-3">
            <p class="text-sm font-medium text-gray-900 dark:text-white">Administrator</p>
            <p class="text-xs text-gray-600 dark:text-gray-400">System Admin</p>
        </div>
    </div>
    <form method="POST" action="<?php echo e(route('admin.logout')); ?>">
        <?php echo csrf_field(); ?>
        <button type="submit" class="p-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="Logout">
            <i class="bi bi-box-arrow-right"></i>
        </button>
    </form>
</div><?php /**PATH /var/www/html/resources/views/admin/layouts/partials/sidebar-footer.blade.php ENDPATH**/ ?>