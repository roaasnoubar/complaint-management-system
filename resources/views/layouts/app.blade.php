<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name')) - Complaint Management</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    @stack('styles')
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen">
    <nav class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center gap-6">
                    <a href="{{ route('dashboard') }}" class="text-xl font-semibold">{{ config('app.name') }}</a>
                    <a href="{{ route('complaints.index') }}" class="text-sm hover:text-blue-600">Complaints</a>
                    <a href="{{ route('users.index') }}" class="text-sm hover:text-blue-600">Users</a>
                    <a href="{{ route('departments.index') }}" class="text-sm hover:text-blue-600">Departments</a>
                    <a href="{{ route('authorities.index') }}" class="text-sm hover:text-blue-600">Authorities</a>
                    <a href="{{ route('roles.index') }}" class="text-sm hover:text-blue-600">Roles</a>
                    <a href="{{ route('permissions.index') }}" class="text-sm hover:text-blue-600">Permissions</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if (session('success'))
            <div class="mb-4 p-4 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200 rounded">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-4 bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200 rounded">{{ session('error') }}</div>
        @endif
        @yield('content')
    </main>
</body>
</html>
