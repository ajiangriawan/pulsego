<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PulseGo - Book. Play. Pulse!</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --pg-green-light: #6DBE4E;
            --pg-green: #3A9E3F;
            --pg-green-dark: #1E6B2A;
            --pg-black: #111827;
            --pg-gray: #6B7280;
            --pg-bg: #F3F4F6;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--pg-bg);
        }

        .pg-gradient {
            background: linear-gradient(135deg, #1E6B2A 0%, #3A9E3F 50%, #6DBE4E 100%);
        }

        .pg-btn-primary {
            background: linear-gradient(135deg, #3A9E3F, #6DBE4E);
            color: white;
            font-weight: 700;
            border-radius: 12px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px rgba(58, 158, 63, 0.35);
        }

        .pg-btn-primary:hover {
            background: linear-gradient(135deg, #1E6B2A, #3A9E3F);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(58, 158, 63, 0.45);
        }

        .pg-card {
            background: white;
            border-radius: 16px;
            border: 1px solid #E5E7EB;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
        }

        .nav-link-active {
            color: var(--pg-green);
            font-weight: 700;
        }

        .bottom-nav-item.active svg,
        .bottom-nav-item.active span {
            color: var(--pg-green);
        }
    </style>
</head>

<body class="bg-gray-100 text-gray-900 antialiased">

    {{-- Desktop Top Navigation --}}
    <div class="hidden md:block">
        <livewire:layout.navigation />
    </div>

    {{-- Main Content --}}
    <main class="pb-24 md:pb-8">
        {{ $slot }}
    </main>

    {{-- Mobile Bottom Navigation --}}
    <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-50 safe-area-bottom">
        <div class="flex justify-around items-center py-2">
            <a href="{{ route('home') }}"
                wire:navigate
                class="bottom-nav-item flex flex-col items-center gap-0.5 px-4 py-1 {{ request()->routeIs('home') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 {{ request()->routeIs('home') ? 'text-green-600' : 'text-gray-400' }}" fill="{{ request()->routeIs('home') ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span class="text-[10px] font-semibold {{ request()->routeIs('home') ? 'text-green-600' : 'text-gray-400' }}">Beranda</span>
            </a>

            <a href="{{ route('booking.history') }}"
                wire:navigate
                class="bottom-nav-item flex flex-col items-center gap-0.5 px-4 py-1 {{ request()->routeIs('booking.history') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 {{ request()->routeIs('booking.history') ? 'text-green-600' : 'text-gray-400' }}" fill="{{ request()->routeIs('booking.history') ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="text-[10px] font-semibold {{ request()->routeIs('booking.history') ? 'text-green-600' : 'text-gray-400' }}">Booking</span>
            </a>

            <a href="{{ route('booking.history.payment') }}"
                class="bottom-nav-item flex flex-col items-center gap-0.5 px-4 py-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                <span class="text-[10px] font-semibold text-gray-400">Bayar</span>
            </a>

            <a href="{{ route('profile') }}"
                wire:navigate
                class="bottom-nav-item flex flex-col items-center gap-0.5 px-4 py-1 {{ request()->routeIs('profile') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 {{ request()->routeIs('profile') ? 'text-green-600' : 'text-gray-400' }}" fill="{{ request()->routeIs('profile') ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span class="text-[10px] font-semibold {{ request()->routeIs('profile') ? 'text-green-600' : 'text-gray-400' }}">Profil</span>
            </a>
        </div>
    </nav>
    @livewire('chatbot')
</body>

</html>