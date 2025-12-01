<?php $__env->startSection('title', 'Game Rooms'); ?>
<?php $__env->startSection('subtitle', 'Manage game rooms'); ?>

<?php $__env->startSection('actions'); ?>
<a href="<?php echo e(route('admin.gamerooms.create')); ?>" class="admin-btn-primary">
    <i class="bi bi-plus-lg mr-1"></i> Create Room
</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="admin-card">
    <div class="admin-card-header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Game Rooms</h2>
            <div class="mt-4 md:mt-0">
                <form method="GET" action="<?php echo e(route('admin.gamerooms.index')); ?>" class="flex space-x-2">
                    <select name="status" class="admin-form-input">
                        <option value="">All Statuses</option>
                        <option value="waiting" <?php echo e(request('status') == 'waiting' ? 'selected' : ''); ?>>
                            Waiting
                        </option>
                        <option value="in_progress" <?php echo e(request('status') == 'in_progress' ? 'selected' : ''); ?>>
                            In Progress
                        </option>
                        <option value="completed" <?php echo e(request('status') == 'completed' ? 'selected' : ''); ?>>
                            Completed
                        </option>
                        <option value="cancelled" <?php echo e(request('status') == 'cancelled' ? 'selected' : ''); ?>>
                            Cancelled
                        </option>
                    </select>
                    <select name="game_type" class="admin-form-input">
                        <option value="">All Types</option>
                        <option value="poker" <?php echo e(request('game_type') == 'poker' ? 'selected' : ''); ?>>
                            Poker
                        </option>
                        <option value="blot" <?php echo e(request('game_type') == 'blot' ? 'selected' : ''); ?>>
                            Blot
                        </option>
                    </select>
                    <button type="submit" class="admin-btn-primary">
                        Filter
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
                        <th>ID</th>
                        <th>Name</th>
                        <th>Game Type</th>
                        <th>Stake</th>
                        <th>Players</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="font-medium text-gray-900 dark:text-white">
                            <?php echo e($room->id); ?>

                        </td>
                        <td>
                            <?php echo e($room->name); ?>

                        </td>
                        <td>
                            <?php if($room->game_type === 'poker'): ?>
                                <span class="admin-badge-primary">Poker</span>
                            <?php elseif($room->game_type === 'blot'): ?>
                                <span class="admin-badge-success">Blot</span>
                            <?php else: ?>
                                <span class="admin-badge-secondary"><?php echo e(ucfirst($room->game_type)); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="font-medium">
                            $<?php echo e(number_format($room->stake, 2)); ?>

                        </td>
                        <td>
                            <?php echo e($room->current_players_count); ?> / <?php echo e($room->max_players); ?>

                        </td>
                        <td>
                            <?php switch($room->status):
                                case ('waiting'): ?>
                                    <span class="admin-badge-warning">Waiting</span>
                                    <?php break; ?>
                                <?php case ('in_progress'): ?>
                                    <span class="admin-badge-primary">In Progress</span>
                                    <?php break; ?>
                                <?php case ('completed'): ?>
                                    <span class="admin-badge-success">Completed</span>
                                    <?php break; ?>
                                <?php case ('cancelled'): ?>
                                    <span class="admin-badge-secondary">Cancelled</span>
                                    <?php break; ?>
                            <?php endswitch; ?>
                        </td>
                        <td>
                            <div class="flex space-x-2">
                                <a href="<?php echo e(route('admin.gamerooms.show', $room)); ?>" 
                                   class="admin-btn-secondary text-sm py-1 px-3">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="<?php echo e(route('admin.gamerooms.edit', $room)); ?>" 
                                   class="admin-btn-secondary text-sm py-1 px-3">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="<?php echo e(route('admin.gamerooms.destroy', $room)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="admin-btn-danger text-sm py-1 px-3"
                                            onclick="return confirm('Are you sure you want to delete this game room?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="7" class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <i class="bi bi-controller text-2xl mb-2 block"></i>
                            No game rooms found
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if($rooms->hasPages()): ?>
        <div class="mt-6">
            <?php echo e($rooms->links()); ?>

        </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/gamerooms/index.blade.php ENDPATH**/ ?>