<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50 dark:bg-gray-900">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'Laravel') }} Admin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Tailwind CSS with custom styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }
        
        .dark::-webkit-scrollbar-track {
            background: #2d3748;
        }
        
        .dark::-webkit-scrollbar-thumb {
            background: #4a5568;
        }
        
        .dark::-webkit-scrollbar-thumb:hover {
            background: #718096;
        }
        
        /* Gradient Animation */
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .gradient-bg {
            background: linear-gradient(-45deg, #3b82f6, #8b5cf6, #ec4899, #f59e0b);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }
        
        /* Pulse Animation */
        .pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); }
            100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
        }
        
        /* Floating Animation */
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        
        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
    </style>
</head>

<body class="h-full transition-colors duration-300">
    <div class="flex h-screen bg-gray-50 dark:bg-gray-900">
        <!-- Sidebar -->
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-30 w-64 transition-all duration-300 transform bg-white dark:bg-gray-800 shadow-xl lg:translate-x-0 lg:static lg:inset-0">
            <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-2">
                    <div class="w-10 h-10 rounded-lg gradient-bg flex items-center justify-center">
                        <i class="bi bi-shield-shaded text-white text-xl"></i>
                    </div>
                    <span class="text-xl font-bold text-gray-800 dark:text-white">{{ config('app.name', 'Laravel') }}</span>
                </a>
                <button id="sidebar-toggle-close" class="lg:hidden text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <i class="bi bi-x-lg text-2xl"></i>
                </button>
            </div>

            <nav class="flex-1 px-3 py-5 overflow-y-auto">
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('admin.dashboard') }}" 
                           class="flex items-center p-3 text-base font-medium rounded-lg transition-all duration-200 group {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                            <i class="bi bi-speedometer2 text-lg mr-3"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="{{ route('admin.users.index') }}" 
                           class="flex items-center p-3 text-base font-medium rounded-lg transition-all duration-200 group {{ request()->routeIs('admin.users.*') ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                            <i class="bi bi-people text-lg mr-3"></i>
                            <span>Users</span>
                            <span class="ml-auto bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full dark:bg-blue-900 dark:text-blue-300">
                                {{ \App\Models\User::count() }}
                            </span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="{{ route('admin.gamerooms.index') }}" 
                           class="flex items-center p-3 text-base font-medium rounded-lg transition-all duration-200 group {{ request()->routeIs('admin.gamerooms.*') ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                            <i class="bi bi-controller text-lg mr-3"></i>
                            <span>Game Rooms</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="{{ route('admin.tournaments.index') }}" 
                           class="flex items-center p-3 text-base font-medium rounded-lg transition-all duration-200 group {{ request()->routeIs('admin.tournaments.*') ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                            <i class="bi bi-trophy text-lg mr-3"></i>
                            <span>Tournaments</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="{{ route('admin.withdrawals.index') }}" 
                           class="flex items-center p-3 text-base font-medium rounded-lg transition-all duration-200 group {{ request()->routeIs('admin.withdrawals.*') ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                            <i class="bi bi-cash-coin text-lg mr-3"></i>
                            <span>Withdrawals</span>
                            @php
                                $pendingCount = \App\Models\Transaction::where('type', 'withdrawal')->where('status', 'pending')->count();
                            @endphp
                            @if ($pendingCount > 0)
                                <span class="ml-auto bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full dark:bg-red-900 dark:text-red-300 animate-pulse">
                                    {{ $pendingCount }}
                                </span>
                            @endif
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white font-bold">
                            {{ substr(auth('admin')->user()->name ?? 'A', 0, 1) }}
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ auth('admin')->user()->name ?? 'Admin' }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Administrator
                            </p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700">
                            <i class="bi bi-box-arrow-right text-lg"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Overlay for mobile -->
        <div id="overlay" class="fixed inset-0 z-20 bg-black bg-opacity-50 hidden lg:hidden"></div>

        <!-- Main Content -->
        <div class="flex flex-col flex-1 w-full overflow-hidden">
            <!-- Header -->
            <header class="z-10 py-4 bg-white shadow-sm dark:bg-gray-800">
                <div class="flex items-center justify-between h-full px-6 mx-auto">
                    <!-- Mobile hamburger -->
                    <button id="sidebar-toggle-open" class="p-2 mr-4 text-gray-600 rounded-lg lg:hidden hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                        <i class="bi bi-list text-2xl"></i>
                    </button>
                    
                    <!-- Search -->
                    <div class="flex-1 max-w-xl mx-4">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <i class="bi bi-search text-gray-400"></i>
                            </div>
                            <input type="text" class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" placeholder="Search...">
                        </div>
                    </div>
                    
                    <!-- Theme Toggle -->
                    <ul class="flex items-center flex-shrink-0 space-x-4">
                        <li class="relative">
                            <button id="theme-toggle" type="button" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 focus:outline-none">
                                <i class="bi bi-sun text-lg dark:hidden"></i>
                                <i class="bi bi-moon text-lg hidden dark:block"></i>
                            </button>
                        </li>
                        
                        <!-- Notifications -->
                        <li class="relative">
                            <button class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 focus:outline-none">
                                <i class="bi bi-bell text-lg"></i>
                                <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">3</span>
                            </button>
                        </li>
                    </ul>
                </div>
            </header>

            <!-- Content Area -->
            <main class="flex-1 overflow-y-auto p-6 bg-gray-50 dark:bg-gray-900 transition-all duration-300">
                <!-- Page Header -->
                <div class="mb-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">@yield('title', 'Dashboard')</h1>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">@yield('subtitle', 'Welcome to your admin dashboard')</p>
                        </div>
                        <div class="mt-4 md:mt-0">
                            @yield('actions')
                        </div>
                    </div>
                </div>

                <!-- Notifications -->
                @include('admin.layouts.partials.notifications')

                <!-- Main Content -->
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        // Sidebar toggle
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const openBtn = document.getElementById('sidebar-toggle-open');
            const closeBtn = document.getElementById('sidebar-toggle-close');
            const overlay = document.getElementById('overlay');
            
            if (openBtn) {
                openBtn.addEventListener('click', function() {
                    sidebar.classList.remove('-translate-x-full');
                    overlay.classList.remove('hidden');
                    document.body.classList.add('overflow-hidden');
                });
            }
            
            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                });
            }
            
            if (overlay) {
                overlay.addEventListener('click', function() {
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                });
            }
            
            // Theme toggle
            const themeToggle = document.getElementById('theme-toggle');
            if (themeToggle) {
                themeToggle.addEventListener('click', function() {
                    document.documentElement.classList.toggle('dark');
                    localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
                });
            }
            
            // Set initial theme
            if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        });
    </script>
    
    @yield('scripts')
</body>

</html>