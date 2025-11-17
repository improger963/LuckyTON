<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'Laravel') }} Admin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Подключаем стили -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="admin-layout">
    <div class="flex h-full">
        <!-- Sidebar -->
        <aside id="adminSidebar" class="admin-sidebar">
            <div class="sidebar-header">
                <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center space-x-2 text-xl font-bold text-gray-800 dark:text-white">
                    <i class="bi bi-shield-shaded text-blue-600"></i>
                    <span>{{ config('app.name', 'Laravel') }}</span>
                </a>
                <button id="sidebarToggle" class="lg:hidden text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <nav class="sidebar-nav">
                @include('admin.layouts.partials.navigation')
            </nav>

            <div class="border-t border-gray-200 dark:border-gray-700 p-4">
                @include('admin.layouts.partials.sidebar-footer')
            </div>
        </aside>

        <!-- Main Content -->
        <div id="adminMain" class="admin-main">
            <!-- Header -->
            <header class="admin-header">
                @include('admin.layouts.partials.header')
            </header>

            <!-- Content Area -->
            <main class="admin-content">
                <!-- Page Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 fade-in">
                    <div class="mb-4 sm:mb-0">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">@yield('title', 'Dashboard')</h1>
                        <p class="text-gray-600 dark:text-gray-400 mt-1">@yield('subtitle', 'Welcome to your admin dashboard')</p>
                    </div>

                    <div class="flex items-center space-x-3">
                        @yield('actions')
                    </div>
                </div>

                <!-- Notifications -->
                @include('admin.layouts.partials.notifications')

                <!-- Main Content -->
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Подключаем JavaScript -->
    @yield('scripts')
</body>

</html>