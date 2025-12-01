<?php $__env->startSection('title', 'Create Tournament'); ?>
<?php $__env->startSection('subtitle', 'Add a new tournament'); ?>

<?php $__env->startSection('actions'); ?>
<a href="<?php echo e(route('admin.tournaments.index')); ?>" class="admin-btn-secondary">
    <i class="bi bi-arrow-left mr-1"></i> Back to Tournaments
</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Create New Tournament</h2>
    </div>
    
    <div class="admin-card-body">
        <form method="POST" action="<?php echo e(route('admin.tournaments.store')); ?>">
            <?php echo csrf_field(); ?>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="admin-form-label">Tournament Name</label>
                    <input type="text" name="name" id="name" 
                           value="<?php echo e(old('name')); ?>"
                           class="admin-form-input" required>
                    <?php $__errorArgs = ['name'];
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
                    <label for="game_type" class="admin-form-label">Game Type</label>
                    <select name="game_type" id="game_type" class="admin-form-input" required>
                        <option value="">Select Game Type</option>
                        <option value="poker" <?php echo e(old('game_type') == 'poker' ? 'selected' : ''); ?>>
                            Poker
                        </option>
                        <option value="blot" <?php echo e(old('game_type') == 'blot' ? 'selected' : ''); ?>>
                            Blot
                        </option>
                    </select>
                    <?php $__errorArgs = ['game_type'];
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
                    <label for="buy_in" class="admin-form-label">Buy-in Amount ($)</label>
                    <input type="number" name="buy_in" id="buy_in" step="0.01" min="0"
                           value="<?php echo e(old('buy_in')); ?>"
                           class="admin-form-input" required>
                    <?php $__errorArgs = ['buy_in'];
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
                    <label for="prize_pool" class="admin-form-label">Prize Pool ($)</label>
                    <input type="number" name="prize_pool" id="prize_pool" step="0.01" min="0"
                           value="<?php echo e(old('prize_pool')); ?>"
                           class="admin-form-input" required>
                    <?php $__errorArgs = ['prize_pool'];
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
                    <label for="max_players" class="admin-form-label">Max Players</label>
                    <input type="number" name="max_players" id="max_players" min="2" max="100"
                           value="<?php echo e(old('max_players', 8)); ?>"
                           class="admin-form-input" required>
                    <?php $__errorArgs = ['max_players'];
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
                    <label for="registration_opens_at" class="admin-form-label">Registration Opens</label>
                    <input type="datetime-local" name="registration_opens_at" id="registration_opens_at"
                           value="<?php echo e(old('registration_opens_at')); ?>"
                           class="admin-form-input" required>
                    <?php $__errorArgs = ['registration_opens_at'];
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
                    <label for="starts_at" class="admin-form-label">Tournament Starts</label>
                    <input type="datetime-local" name="starts_at" id="starts_at"
                           value="<?php echo e(old('starts_at')); ?>"
                           class="admin-form-input" required>
                    <?php $__errorArgs = ['starts_at'];
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
                
                <div class="md:col-span-2">
                    <label for="description" class="admin-form-label">Description</label>
                    <textarea name="description" id="description" rows="3"
                              class="admin-form-input"><?php echo e(old('description')); ?></textarea>
                    <?php $__errorArgs = ['description'];
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
                    <i class="bi bi-plus-circle mr-1"></i> Create Tournament
                </button>
                <a href="<?php echo e(route('admin.tournaments.index')); ?>" class="admin-btn-secondary ml-2">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/tournaments/create.blade.php ENDPATH**/ ?>