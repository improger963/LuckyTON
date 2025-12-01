<?php $__env->startSection('title', 'Users'); ?>
<?php $__env->startSection('subtitle', 'Manage user accounts'); ?>

<?php $__env->startSection('actions'); ?>
<a href="<?php echo e(route('admin.users.create')); ?>" class="admin-btn-primary">
    <i class="bi bi-plus-lg mr-2"></i> Add User
</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="admin-card fade-in">
    <div class="admin-card-header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Users</h2>
            <div class="mt-4 md:mt-0">
                <form method="GET" action="<?php echo e(route('admin.users.index')); ?>" class="flex space-x-2">
                    <div class="relative">
                        <input type="text" name="search" value="<?php echo e(request('search')); ?>" 
                               placeholder="Search users..." 
                               class="admin-form-input pl-10 w-64">
                        <i class="bi bi-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <button type="submit" class="admin-btn-primary">
                        <i class="bi bi-funnel"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="admin-card-body">
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Balance</th>
                        <th>Referrals</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td>
                            <div class="flex items-center">
                                <div class="admin-user-avatar admin-user-avatar-sm">
                                    <?php echo e(substr($user->username ?? $user->email, 0, 1)); ?>

                                </div>
                                <div class="ml-3">
                                    <p class="font-medium text-gray-900 dark:text-white">
                                        <?php echo e($user->username ?? 'N/A'); ?>

                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        ID: <?php echo e($user->id); ?>

                                    </p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php echo e($user->email); ?>

                        </td>
                        <td>
                            <?php if($user->wallet): ?>
                                <span class="font-medium text-green-600 dark:text-green-400">
                                    $<?php echo e(number_format($user->wallet->balance, 2)); ?>

                                </span>
                            <?php else: ?>
                                <span class="text-gray-500 dark:text-gray-400">
                                    $0.00
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="font-medium">
                                <?php echo e($user->referrals()->count()); ?>

                            </span>
                        </td>
                        <td>
                            <?php if($user->banned_at): ?>
                                <span class="admin-badge-danger">Banned</span>
                            <?php else: ?>
                                <span class="admin-badge-success">Active</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="flex space-x-2">
                                <a href="<?php echo e(route('admin.users.show', $user)); ?>" 
                                   class="admin-btn-secondary text-sm py-1.5 px-3"
                                   title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="<?php echo e(route('admin.users.edit', $user)); ?>" 
                                   class="admin-btn-secondary text-sm py-1.5 px-3"
                                   title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php if($user->banned_at): ?>
                                    <form method="POST" action="<?php echo e(route('admin.users.unban', $user)); ?>">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="admin-btn-success text-sm py-1.5 px-3"
                                                onclick="return confirm('Are you sure you want to unban this user?')"
                                                title="Unban">
                                            <i class="bi bi-unlock"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="<?php echo e(route('admin.users.ban', $user)); ?>">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="admin-btn-danger text-sm py-1.5 px-3"
                                                onclick="return confirm('Are you sure you want to ban this user?')"
                                                title="Ban">
                                            <i class="bi bi-lock"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="6" class="text-center py-12 text-gray-500 dark:text-gray-400">
                            <i class="bi bi-person-x text-3xl mb-3 block"></i>
                            <p>No users found</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if($users->hasPages()): ?>
        <div class="mt-6">
            <?php echo e($users->links()); ?>

        </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/users/index.blade.php ENDPATH**/ ?>