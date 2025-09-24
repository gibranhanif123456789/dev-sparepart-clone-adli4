<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Aplikasi Spare Part')</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">


    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        .font-sans {
            font-family: 'Figtree', sans-serif;
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50">
    <div x-data="{
        sidebarExpanded: false,
        showNotification: false,
        notificationMessage: '',
        pendingCount: 0,
        lastCount: 0,

        checkPendingRequests() {
        axios.get('{{ route('kepalaro.api.pending.count') }}')
            .then(response => {
                this.pendingCount = response.data.count;
                if (this.pendingCount > this.lastCount) {
                    this.notificationMessage = `Ada ${this.pendingCount - this.lastCount} permintaan baru!`;
                    this.showNotification = true;
                    setTimeout(() => { this.showNotification = false; }, 5000);
                }
                this.lastCount = this.pendingCount;
            })
            .catch(err => console.error('Gagal cek permintaan:', err));
    }
    }"
    x-init="() => {
        checkPendingRequests();
        setInterval(checkPendingRequests, 10000);
    }">

        <!-- Sidebar -->
        <aside @mouseenter="sidebarExpanded = true"
               @mouseleave="sidebarExpanded = false"
               :class="sidebarExpanded ? 'w-60' : 'w-16'"
               class="fixed inset-y-0 left-0 z-50 bg-[#001438] text-white shadow-lg transition-all duration-300 flex flex-col"
               style="top: 0; bottom: 0;">

            <!-- Logo -->
            <div class="flex items-center justify-center h-16 border-b border-blue-900 bg-[#002060]">
                <span x-show="!sidebarExpanded" class="text-lg font-bold">
                    <img src="{{ asset('images/logo-pgn.png') }}" alt="PGN Logo" class="h-6">
                </span>
                <div x-show="sidebarExpanded" class="flex items-center space-x-2">
                    <img src="{{ asset('images/logo-pgn.png') }}" alt="PGN Logo" class="h-6">
                    <span class="text-sm font-semibold">AMS</span>
                </div>
            </div>

            <!-- Menu -->
            <nav class="mt-6 px-2 flex-1">
                <a href="{{ route('kepalaro.dashboard') }}"
                   class="flex items-center px-3 py-2 rounded transition duration-200 hover:bg-blue-900 text-gray-300 hover:text-white"
                   :class="{ 'bg-blue-900 text-white': '{{ request()->routeIs('kepalaro.dashboard') ? 'true' : 'false' }}' === 'true' }">
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v0h-8v0z" />
                        </svg>
                        <span x-show="!sidebarExpanded && pendingCount > 0"
                              class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
                    </div>
                    <span x-show="sidebarExpanded" class="ml-2 text-sm">Dashboard</span>
                    <span x-show="sidebarExpanded && pendingCount > 0"
                          class="ml-auto bg-red-500 text-white text-xs font-medium w-5 h-5 flex items-center justify-center rounded-full">
                        <template x-if="pendingCount < 10" x-text="pendingCount"></template>
                        <template x-if="pendingCount >= 10">!</template>
                    </span>
                </a>

                <a href="{{ route('kepalaro.history') }}"
                   class="flex items-center px-3 py-2 mt-2 rounded transition duration-200 hover:bg-blue-900 text-gray-300 hover:text-white"
                   :class="{ 'bg-blue-900 text-white': '{{ request()->routeIs('kepalaro.history') ? 'true' : 'false' }}' === 'true' }">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span x-show="sidebarExpanded" class="ml-3 text-sm">History</span>
                </a>
            </nav>

            <!-- Profile -->
            <div class="p-3 border-t border-blue-900 relative"
                 x-data="{ showDropdown: false }"
                 @mouseenter="showDropdown = true"
                 @mouseleave="showDropdown = false">
                <div class="bg-white text-gray-800 rounded-lg p-2 flex items-center space-x-2 text-sm cursor-pointer"
                     :class="{ 'justify-center': !sidebarExpanded }">
                    <img src="{{ asset('images/avatar.png') }}" alt="Profile" class="w-8 h-8 rounded-full">
                    <div x-show="sidebarExpanded" class="truncate ml-2">
                        <div class="font-medium">Kepala RO</div>
                        <div class="text-xs text-gray-600">Kepala RO</div>
                    </div>
                </div>
                <div x-show="showDropdown" x-transition
                     class="absolute bottom-16 left-4 bg-white text-gray-800 rounded-lg shadow-lg w-48 z-50"
                     x-cloak>
                   <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-sm hover:bg-gray-100">
    Profil Saya
</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Logout</button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main :class="sidebarExpanded ? 'md:ml-60' : 'md:ml-16'"
              class="flex-1 transition-all duration-300 overflow-y-auto">
            @yield('content')
        </main>

        <!-- Toast Notification -->
        <div x-show="showNotification"
             x-transition
             class="fixed bottom-5 right-5 bg-blue-600 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center space-x-2"
             x-cloak>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM12 19a7 7 0 110-14 7 7 0 010 14z" />
            </svg>
            <span x-text="notificationMessage"></span>
        </div>
    </div>
</body>
</html>