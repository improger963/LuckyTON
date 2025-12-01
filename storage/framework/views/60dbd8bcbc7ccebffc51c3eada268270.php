<?php $__env->startSection('title', 'Withdrawals'); ?>
<?php $__env->startSection('subtitle', 'Manage withdrawal requests'); ?>

<?php $__env->startSection('content'); ?>
<div class="admin-card">
    <div class="admin-card-header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Withdrawal Requests</h2>
            <div class="mt-4 md:mt-0">
                <form method="GET" action="<?php echo e(route('admin.withdrawals.index')); ?>" class="flex space-x-2">
                    <select name="status" class="admin-form-input">
                        <option value="">All Statuses</option>
                        <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>
                            Pending
                        </option>
                        <option value="completed" <?php echo e(request('status') == 'completed' ? 'selected' : ''); ?>>
                            Completed
                        </option>
                        <option value="cancelled" <?php echo e(request('status') == 'cancelled' ? 'selected' : ''); ?>>
                            Cancelled
                        </option>
                        <option value="failed" <?php echo e(request('status') == 'failed' ? 'selected' : ''); ?>>
                            Failed
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
                        <th>User</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $withdrawals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $withdrawal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="font-medium text-gray-900 dark:text-white">
                            <?php echo e($withdrawal->id); ?>

                        </td>
                        <td>
                            <?php if($withdrawal->wallet && $withdrawal->wallet->user): ?>
                                <?php echo e($withdrawal->wallet->user->username ?? $withdrawal->wallet->user->email); ?>

                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td class="font-medium">
                            $<?php echo e(number_format($withdrawal->amount, 2)); ?>

                        </td>
                        <td>
                            <?php if(isset($withdrawal->metadata['method'])): ?>
                                <?php echo e(ucfirst($withdrawal->metadata['method'])); ?>

                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo e($withdrawal->created_at->format('M d, Y H:i')); ?>

                        </td>
                        <td>
                            <?php switch($withdrawal->status):
                                case ('pending'): ?>
                                    <span class="admin-badge-warning">Pending</span>
                                    <?php break; ?>
                                <?php case ('completed'): ?>
                                    <span class="admin-badge-success">Completed</span>
                                    <?php break; ?>
                                <?php case ('cancelled'): ?>
                                    <span class="admin-badge-secondary">Cancelled</span>
                                    <?php break; ?>
                                <?php case ('failed'): ?>
                                    <span class="admin-badge-danger">Failed</span>
                                    <?php break; ?>
                            <?php endswitch; ?>
                        </td>
                        <td>
                            <div class="flex space-x-2">
                                <a href="<?php echo e(route('admin.withdrawals.show', $withdrawal)); ?>" 
                                   class="admin-btn-secondary text-sm py-1 px-3">
                                    <i class="bi bi-eye"></i>
                                </a>
                                
                                <?php if($withdrawal->status === 'pending'): ?>
                                    <form method="POST" action="<?php echo e(route('admin.withdrawals.approve', $withdrawal)); ?>">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="admin-btn-success text-sm py-1 px-3"
                                                onclick="return confirm('Are you sure you want to approve this withdrawal?')">
                                            <i class="bi bi-check"></i>
                                        </button>
                                    </form>
                                    
                                    <form method="POST" action="<?php echo e(route('admin.withdrawals.reject', $withdrawal)); ?>">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="admin-btn-danger text-sm py-1 px-3"
                                                onclick="return confirm('Are you sure you want to reject this withdrawal?')">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="7" class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <i class="bi bi-cash-coin text-2xl mb-2 block"></i>
                            No withdrawal requests found
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if($withdrawals->hasPages()): ?>
        <div class="mt-6">
            <?php echo e($withdrawals->links()); ?>

        </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/withdrawals/index.blade.php ENDPATH**/ ?>