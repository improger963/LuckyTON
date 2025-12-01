<?php $__env->startSection('title', 'User Details'); ?>
<?php $__env->startSection('subtitle', 'View user information'); ?>

<?php $__env->startSection('actions'); ?>
<a href="<?php echo e(route('admin.users.index')); ?>" class="admin-btn-secondary">
    <i class="bi bi-arrow-left mr-1"></i> Back to Users
</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- User Info Card -->
    <div class="lg:col-span-1">
        <div class="admin-card">
            <div class="admin-card-body">
                <div class="flex flex-col items-center">
                    <div class="w-24 h-24 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center mb-4">
                        <i class="bi bi-person text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                        <?php echo e($user->username ?? $user->email); ?>

                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">
                        Member since <?php echo e($user->created_at->format('M d, Y')); ?>

                    </p>
                    
                    <div class="mt-6 w-full space-y-4">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Status:</span>
                            <?php if($user->banned_at): ?>
                                <span class="admin-badge-danger">Banned</span>
                            <?php else: ?>
                                <span class="admin-badge-success">Active</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Premium:</span>
                            <?php if($user->is_premium): ?>
                                <span class="admin-badge-success">Yes</span>
                            <?php else: ?>
                                <span class="admin-badge-secondary">No</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">PIN Enabled:</span>
                            <?php if($user->is_pin_enabled): ?>
                                <span class="admin-badge-success">Yes</span>
                            <?php else: ?>
                                <span class="admin-badge-secondary">No</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Balance Card -->
        <div class="admin-card mt-6">
            <div class="admin-card-header">
                <h3 class="font-semibold text-gray-900 dark:text-white">Wallet</h3>
            </div>
            <div class="admin-card-body">
                <?php if($user->wallet): ?>
                    <div class="text-center">
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">
                            $<?php echo e(number_format($user->wallet->balance, 2)); ?>

                        </p>
                        <p class="text-gray-600 dark:text-gray-400 mt-2">
                            Wallet Address: <?php echo e($user->wallet->address); ?>

                        </p>
                        <div class="mt-4">
                            <a href="#" class="admin-btn-primary">
                                <i class="bi bi-currency-dollar mr-1"></i> Adjust Balance
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-center py-4 text-gray-500 dark:text-gray-400">
                        No wallet found
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- User Details -->
    <div class="lg:col-span-2">
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="font-semibold text-gray-900 dark:text-white">User Information</h3>
            </div>
            <div class="admin-card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="admin-form-label">Username</label>
                        <p class="text-gray-900 dark:text-white"><?php echo e($user->username ?? 'N/A'); ?></p>
                    </div>
                    
                    <div>
                        <label class="admin-form-label">Email</label>
                        <p class="text-gray-900 dark:text-white"><?php echo e($user->email); ?></p>
                    </div>
                    
                    <div>
                        <label class="admin-form-label">Referral Code</label>
                        <p class="text-gray-900 dark:text-white"><?php echo e($user->referral_code ?? 'N/A'); ?></p>
                    </div>
                    
                    <div>
                        <label class="admin-form-label">Referred By</label>
                        <p class="text-gray-900 dark:text-white">
                            <?php if($user->referrer): ?>
                                <?php echo e($user->referrer->username ?? $user->referrer->email); ?>

                            <?php else: ?>
                                None
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <div>
                        <label class="admin-form-label">Total Referrals</label>
                        <p class="text-gray-900 dark:text-white"><?php echo e($user->referrals()->count()); ?></p>
                    </div>
                    
                    <div>
                        <label class="admin-form-label">Total Earnings</label>
                        <p class="text-gray-900 dark:text-white">$<?php echo e(number_format($user->total_referral_earnings, 2)); ?></p>
                    </div>
                </div>
                
                <div class="mt-6">
                    <label class="admin-form-label">Last Updated</label>
                    <p class="text-gray-900 dark:text-white">
                        <?php echo e($user->updated_at->format('M d, Y H:i:s')); ?>

                    </p>
                </div>
            </div>
        </div>
        
        <!-- Referrals -->
        <div class="admin-card mt-6">
            <div class="admin-card-header">
                <h3 class="font-semibold text-gray-900 dark:text-white">Referrals</h3>
            </div>
            <div class="admin-card-body">
                <?php if($user->referrals()->count() > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Earnings</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $user->referrals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $referral): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($referral->username ?? 'N/A'); ?></td>
                                    <td><?php echo e($referral->email); ?></td>
                                    <td>
                                        $<?php echo e(number_format($referral->total_referral_earnings, 2)); ?>

                                    </td>
                                    <td><?php echo e($referral->created_at->format('M d, Y')); ?></td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center py-4 text-gray-500 dark:text-gray-400">
                        No referrals found
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/users/show.blade.php ENDPATH**/ ?>