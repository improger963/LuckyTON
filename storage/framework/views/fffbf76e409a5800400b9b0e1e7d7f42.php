
<a href="<?php echo e(route('admin.dashboard')); ?>"
    class="admin-nav-link <?php echo e(request()->routeIs('admin.dashboard') ? 'active' : ''); ?>">
    <i class="bi bi-speedometer2"></i>
    <span>Dashboard</span>
</a>

<a href="<?php echo e(route('admin.users.index')); ?>"
    class="admin-nav-link <?php echo e(request()->routeIs('admin.users.*') ? 'active' : ''); ?>">
    <i class="bi bi-people"></i>
    <span>Users</span>
    <span class="ml-auto admin-badge-primary">
        <?php echo e(\App\Models\User::count()); ?>

    </span>
</a>

<a href="<?php echo e(route('admin.withdrawals.index')); ?>"
    class="admin-nav-link <?php echo e(request()->routeIs('admin.withdrawals.*') ? 'active' : ''); ?>">
    <i class="bi bi-cash-coin"></i>
    <span>Withdrawals</span>
    <?php
        $pendingCount = \App\Models\Transaction::where('type', 'withdrawal')->where('status', 'pending')->count();
    ?>
    <?php if($pendingCount > 0): ?>
        <span class="ml-auto admin-badge-danger">
            <?php echo e($pendingCount); ?>

        </span>
    <?php endif; ?>
</a>

<a href="<?php echo e(route('admin.gamerooms.index')); ?>"
    class="admin-nav-link <?php echo e(request()->routeIs('admin.gamerooms.*') ? 'active' : ''); ?>">
    <i class="bi bi-controller"></i>
    <span>Game Rooms</span>
</a>

<a href="<?php echo e(route('admin.tournaments.index')); ?>"
    class="admin-nav-link <?php echo e(request()->routeIs('admin.tournaments.*') ? 'active' : ''); ?>">
    <i class="bi bi-trophy"></i>
    <span>Tournaments</span>
</a><?php /**PATH /var/www/html/resources/views/admin/layouts/partials/navigation.blade.php ENDPATH**/ ?>