<?php $__env->startSection('title', 'Edit User'); ?>
<?php $__env->startSection('subtitle', 'Modify user information'); ?>

<?php $__env->startSection('actions'); ?>
<a href="<?php echo e(route('admin.users.index')); ?>" class="admin-btn-secondary">
    <i class="bi bi-arrow-left mr-1"></i> Back to Users
</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Edit User</h2>
    </div>
    
    <div class="admin-card-body">
        <form method="POST" action="<?php echo e(route('admin.users.update', $user)); ?>">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="username" class="admin-form-label">Username</label>
                    <input type="text" name="username" id="username" 
                           value="<?php echo e(old('username', $user->username)); ?>"
                           class="admin-form-input">
                    <?php $__errorArgs = ['username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                
                <div>
                    <label for="email" class="admin-form-label">Email</label>
                    <input type="email" name="email" id="email" 
                           value="<?php echo e(old('email', $user->email)); ?>"
                           class="admin-form-input">
                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                
                <div>
                    <label for="is_premium" class="admin-form-label">Premium Status</label>
                    <select name="is_premium" id="is_premium" class="admin-form-input">
                        <option value="0" <?php echo e(old('is_premium', $user->is_premium) == 0 ? 'selected' : ''); ?>>
                            Regular User
                        </option>
                        <option value="1" <?php echo e(old('is_premium', $user->is_premium) == 1 ? 'selected' : ''); ?>>
                            Premium User
                        </option>
                    </select>
                    <?php $__errorArgs = ['is_premium'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                
                <div>
                    <label for="is_pin_enabled" class="admin-form-label">PIN Enabled</label>
                    <select name="is_pin_enabled" id="is_pin_enabled" class="admin-form-input">
                        <option value="0" <?php echo e(old('is_pin_enabled', $user->is_pin_enabled) == 0 ? 'selected' : ''); ?>>
                            Disabled
                        </option>
                        <option value="1" <?php echo e(old('is_pin_enabled', $user->is_pin_enabled) == 1 ? 'selected' : ''); ?>>
                            Enabled
                        </option>
                    </select>
                    <?php $__errorArgs = ['is_pin_enabled'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
            
            <div class="mt-6">
                <button type="submit" class="admin-btn-primary">
                    <i class="bi bi-save mr-1"></i> Save Changes
                </button>
                <a href="<?php echo e(route('admin.users.index')); ?>" class="admin-btn-secondary ml-2">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Ban/Unban Form -->
<div class="admin-card mt-6">
    <div class="admin-card-header">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Account Status</h2>
    </div>
    
    <div class="admin-card-body">
        <?php if($user->banned_at): ?>
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-medium text-gray-900 dark:text-white">User is Banned</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">
                        Banned on <?php echo e($user->banned_at->format('M d, Y H:i:s')); ?>

                    </p>
                </div>
                <form method="POST" action="<?php echo e(route('admin.users.unban', $user)); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="admin-btn-success">
                        <i class="bi bi-unlock mr-1"></i> Unban User
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-medium text-gray-900 dark:text-white">User is Active</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">
                        Account is currently active
                    </p>
                </div>
                <form method="POST" action="<?php echo e(route('admin.users.ban', $user)); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="admin-btn-danger"
                            onclick="return confirm('Are you sure you want to ban this user?')">
                        <i class="bi bi-lock mr-1"></i> Ban User
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Balance Adjustment -->
<div class="admin-card mt-6">
    <div class="admin-card-header">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Adjust Balance</h2>
    </div>
    
    <div class="admin-card-body">
        <form method="POST" action="<?php echo e(route('admin.users.balance.adjust', $user)); ?>">
            <?php echo csrf_field(); ?>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="amount" class="admin-form-label">Amount</label>
                    <input type="number" name="amount" id="amount" step="0.01"
                           class="admin-form-input" placeholder="0.00">
                    <?php $__errorArgs = ['amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                
                <div>
                    <label for="type" class="admin-form-label">Type</label>
                    <select name="type" id="type" class="admin-form-input">
                        <option value="add">Add Funds</option>
                        <option value="subtract">Subtract Funds</option>
                    </select>
                </div>
                
                <div>
                    <label for="reason" class="admin-form-label">Reason</label>
                    <input type="text" name="reason" id="reason" 
                           class="admin-form-input" placeholder="Reason for adjustment">
                    <?php $__errorArgs = ['reason'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="admin-btn-primary">
                    <i class="bi bi-currency-dollar mr-1"></i> Adjust Balance
                </button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/users/edit.blade.php ENDPATH**/ ?>