<nav class="border-b border-slate-200 bg-white shadow-sm">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center gap-4">
            {{-- Con sesión: al panel (los usuarios finales son redirigidos al catálogo). Sin sesión: a la página de inicio. --}}
            <a href="{{ auth()->check() ? route('dashboard') : route('welcome') }}" class="flex h-9 w-9 items-center justify-center rounded-md text-[#14532d]" aria-label="Inicio">
                @if (file_exists(public_path('build/images/logo-cotecnova.png')))
                    <img src="{{ asset('build/images/logo-cotecnova.png') }}" alt="Logo COTECNOVA" class="h-9 w-9 object-contain" />
                @elseif (file_exists(public_path('build/images/logo-cotecnova.svg')))
                    <img src="{{ asset('build/images/logo-cotecnova.svg') }}" alt="Logo COTECNOVA" class="h-9 w-9 object-contain" />
                @else
                    <x-application-logo class="h-9 w-9" />
                @endif
            </a>

            <div class="hidden items-center gap-6 text-sm font-medium sm:flex">
                <span class="text-slate-500">Biblioteca COTECNOVA</span>
            </div>
        </div>

        @auth
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-[#dcfce7] text-[11px] font-bold text-[#14532d]">
                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                </div>

                <div class="hidden min-w-0 text-right sm:block">
                    <div class="truncate text-sm font-semibold text-slate-900">{{ Auth::user()->name }}</div>
                    <div class="text-[10px] uppercase tracking-[0.18em] text-slate-500">
                        {{ Auth::user()->esAdministrador() ? 'Administrador' : 'Usuario' }}
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="ml-1">
                    @csrf
                    <button type="submit" class="inline-flex items-center rounded-md border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-[#14532d] hover:text-[#14532d]">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        @endauth

        {{-- Visitantes: como el catálogo es público, necesitan cómo entrar --}}
        @guest
            <div class="flex items-center gap-2">
                <a href="{{ route('login') }}" class="inline-flex items-center rounded-md border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-[#14532d] hover:text-[#14532d]">
                    Iniciar sesión
                </a>
                <a href="{{ route('register') }}" class="inline-flex items-center rounded-md bg-[#14532d] px-3 py-2 text-xs font-semibold text-white transition hover:bg-[#166534]">
                    Registrarme
                </a>
            </div>
        @endguest
    </div>
</nav>