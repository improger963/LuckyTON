{{-- Файл: resources/views/admin/layouts/partials/header.blade.php --}}
<div class="flex items-center">
    <button id="mobileMenuToggle" class="lg:hidden text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 mr-4">
        <i class="bi bi-list text-xl"></i>
    </button>
    <div class="relative hidden md:block">
        <input type="text" placeholder="Search..." class="admin-form-input pl-10 w-64">
        <i class="bi bi-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
    </div>
</div>

<div class="flex items-center space-x-4">
    <button class="relative p-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
        <i class="bi bi-bell text-xl"></i>
        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">3</span>
    </button>

    <div class="relative group">
        <button class="flex items-center space-x-2 admin-btn-secondary">
            <div class="admin-user-avatar admin-user-avatar-sm">
                A
            </div>
            <span class="hidden md:inline">Admin</span>
            <i class="bi bi-chevron-down text-xs"></i>
        </button>
        <div class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg py-2 hidden group-hover:block z-50">
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                <i class="bi bi-person mr-2"></i> Profile
            </a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                <i class="bi bi-gear mr-2"></i> Settings
            </a>
            <form method="POST" action="{{ route('admin.logout') }}" class="border-t border-gray-200 dark:border-gray-700 mt-2 pt-2">
                @csrf
                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                    <i class="bi bi-box-arrow-right mr-2"></i> Logout
                </button>
            </form>
        </div>
    </div>
</div>