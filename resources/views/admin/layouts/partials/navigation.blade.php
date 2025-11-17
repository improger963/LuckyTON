{{-- Файл: resources/views/admin/layouts/partials/navigation.blade.php --}}
<a href="{{ route('admin.dashboard') }}"
    class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
    <i class="bi bi-speedometer2"></i>
    <span>Dashboard</span>
</a>

<a href="{{ route('admin.users.index') }}"
    class="admin-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
    <i class="bi bi-people"></i>
    <span>Users</span>
    <span class="ml-auto admin-badge-primary">
        {{ \App\Models\User::count() }}
    </span>
</a>

<a href="{{ route('admin.withdrawals.index') }}"
    class="admin-nav-link {{ request()->routeIs('admin.withdrawals.*') ? 'active' : '' }}">
    <i class="bi bi-cash-coin"></i>
    <span>Withdrawals</span>
    @php
        $pendingCount = \App\Models\Transaction::where('type', 'withdrawal')->where('status', 'pending')->count();
    @endphp
    @if ($pendingCount > 0)
        <span class="ml-auto admin-badge-danger">
            {{ $pendingCount }}
        </span>
    @endif
</a>

<a href="{{ route('admin.gamerooms.index') }}"
    class="admin-nav-link {{ request()->routeIs('admin.gamerooms.*') ? 'active' : '' }}">
    <i class="bi bi-controller"></i>
    <span>Game Rooms</span>
</a>

<a href="{{ route('admin.tournaments.index') }}"
    class="admin-nav-link {{ request()->routeIs('admin.tournaments.*') ? 'active' : '' }}">
    <i class="bi bi-trophy"></i>
    <span>Tournaments</span>
</a>