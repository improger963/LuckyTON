<?php $__env->startSection('title', 'Dashboard'); ?>
<?php $__env->startSection('subtitle', 'Welcome to your admin dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Total Users Card -->
    <div class="admin-stat-card fade-in">
        <div class="flex items-center">
            <div class="stat-icon">
                <i class="bi bi-people text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="admin-stat-number"><?php echo e($stats['total_users'] ?? 0); ?></h3>
                <p class="admin-stat-label">Total Users</p>
                <div class="admin-stat-trend up">
                    <i class="bi bi-arrow-up mr-1"></i>
                    <span>+<?php echo e($stats['new_users_today'] ?? 0); ?> today</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Game Rooms Card -->
    <div class="admin-stat-card fade-in">
        <div class="flex items-center">
            <div class="stat-icon">
                <i class="bi bi-controller text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="admin-stat-number"><?php echo e($stats['total_rooms'] ?? 0); ?></h3>
                <p class="admin-stat-label">Game Rooms</p>
                <div class="admin-stat-trend up">
                    <i class="bi bi-arrow-up mr-1"></i>
                    <span>Active</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Withdrawals Card -->
    <div class="admin-stat-card fade-in">
        <div class="flex items-center">
            <div class="stat-icon">
                <i class="bi bi-cash-coin text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="admin-stat-number"><?php echo e($stats['pending_withdrawals'] ?? 0); ?></h3>
                <p class="admin-stat-label">Pending Withdrawals</p>
                <?php if(($stats['pending_withdrawals'] ?? 0) > 0): ?>
                <div class="admin-stat-trend down">
                    <i class="bi bi-exclamation-triangle mr-1"></i>
                    <span>Attention needed</span>
                </div>
                <?php else: ?>
                <div class="admin-stat-trend up">
                    <i class="bi bi-check-circle mr-1"></i>
                    <span>All clear</span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Total Tournaments Card -->
    <div class="admin-stat-card fade-in">
        <div class="flex items-center">
            <div class="stat-icon">
                <i class="bi bi-trophy text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="admin-stat-number"><?php echo e($stats['total_tournaments'] ?? 0); ?></h3>
                <p class="admin-stat-label">Tournaments</p>
                <div class="admin-stat-trend up">
                    <i class="bi bi-arrow-up mr-1"></i>
                    <span>Active</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Users -->
    <div class="admin-card fade-in">
        <div class="admin-card-header">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Users</h2>
                <a href="<?php echo e(route('admin.users.index')); ?>" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                    View all
                </a>
            </div>
        </div>
        <div class="admin-card-body">
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Registered</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $latestUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <div class="flex items-center">
                                    <div class="admin-user-avatar admin-user-avatar-sm">
                                        <?php echo e(substr($user->username ?? $user->email, 0, 1)); ?>

                                    </div>
                                    <div class="ml-3">
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            <?php echo e($user->username ?? $user->email); ?>

                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php echo e($user->created_at->format('M d, Y')); ?>

                            </td>
                            <td>
                                <?php if($user->banned_at): ?>
                                    <span class="admin-badge-danger">Banned</span>
                                <?php else: ?>
                                    <span class="admin-badge-success">Active</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="3" class="text-center py-4 text-gray-500 dark:text-gray-400">
                                No users found
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="admin-card fade-in">
        <div class="admin-card-header">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Activities</h2>
        </div>
        <div class="admin-card-body">
            <div class="space-y-4">
                <?php $__empty_1 = true; $__currentLoopData = $recentActivities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="admin-activity-item">
                    <div class="admin-activity-icon">
                        <i class="bi bi-activity"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-900 dark:text-white"><?php echo e($activity['description'] ?? 'Activity'); ?></p>
                        <p class="text-sm text-gray-500 dark:text-gray-400"><?php echo e($activity['user'] ?? 'Unknown User'); ?></p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1"><?php echo e($activity['time'] ?? 'Just now'); ?></p>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-center py-4 text-gray-500 dark:text-gray-400">No recent activities</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/dashboard/index.blade.php ENDPATH**/ ?>