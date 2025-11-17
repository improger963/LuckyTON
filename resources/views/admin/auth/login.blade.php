<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - {{ config('app.name', 'Laravel') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Подключаем стили -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="h-full bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
    <div class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="flex justify-center">
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg">
                    <i class="bi bi-shield-shaded text-white text-2xl"></i>
                </div>
            </div>
            <h2 class="mt-6 text-center text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                Admin Portal
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                Sign in to your administrator account
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white dark:bg-gray-800 py-8 px-4 shadow-xl sm:rounded-lg sm:px-10 transition-all duration-300 hover:shadow-2xl">
                @if(session('error'))
                <div class="admin-alert-error mb-6 fade-in">
                    <div class="flex items-center">
                        <i class="bi bi-exclamation-circle mr-2"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button type="button" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300"
                            onclick="this.parentElement.remove()">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                @endif

                @if($errors->any())
                <div class="admin-alert-error mb-6 fade-in">
                    <div>
                        <div class="flex items-center">
                            <i class="bi bi-exclamation-circle mr-2"></i>
                            <span>Please correct the errors below:</span>
                        </div>
                        <ul class="list-disc list-inside mt-2 text-sm">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300"
                            onclick="this.parentElement.remove()">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                @endif

                <form class="space-y-6" method="POST" action="{{ route('admin.login.store') }}">
                    @csrf
                    
                    <div>
                        <label for="email" class="admin-form-label">Email address</label>
                        <div class="mt-1 relative">
                            <input id="email" name="email" type="email" autocomplete="email" required
                                class="admin-form-input pl-10">
                            <i class="bi bi-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                    </div>

                    <div>
                        <label for="password" class="admin-form-label">Password</label>
                        <div class="mt-1 relative">
                            <input id="password" name="password" type="password" autocomplete="current-password" required
                                class="admin-form-input pl-10">
                            <i class="bi bi-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember" name="remember" type="checkbox"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="remember" class="ml-2 block text-sm text-gray-900 dark:text-white">
                                Remember me
                            </label>
                        </div>
                        
                        <div class="text-sm">
                            <a href="#" class="font-medium text-blue-600 hover:text-blue-500">
                                Forgot password?
                            </a>
                        </div>
                    </div>

                    <div>
                        <button type="submit"
                            class="admin-btn-primary w-full flex justify-center py-3 px-4">
                            <i class="bi bi-box-arrow-in-right mr-2"></i> Sign in
                        </button>
                    </div>
                </form>
                
                <div class="mt-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400">
                                Secure Admin Portal
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>