<style>
    .cotec-topbar { border-bottom: 1px solid #dce8df; background: rgba(255,255,255,.97); box-shadow: 0 4px 16px rgba(15,23,42,.045); }
    .cotec-topbar-inner { display: grid; grid-template-columns: minmax(0,1fr) auto minmax(0,1fr); align-items: center; gap: 1rem; max-width: 1440px; min-height: 76px; margin: 0 auto; padding: .65rem 1.5rem; }
    .cotec-brand { display: flex; min-width: 0; align-items: center; gap: .75rem; }
    .cotec-brand-logo { display: flex; width: 42px; height: 42px; flex: 0 0 42px; align-items: center; justify-content: center; }
    .cotec-brand-name { display: flex; flex-direction: column; border-left: 1px solid #dce8df; padding-left: .75rem; line-height: 1.15; }
    .cotec-brand-name span:first-child { color: #64748b; font-size: .62rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
    .cotec-brand-name span:last-child { color: #14532d; font-size: .92rem; font-weight: 800; letter-spacing: .035em; }
    .cotec-topbar-groups { display: flex; align-items: center; justify-content: center; gap: .3rem; padding: .28rem; border: 1px solid #e4eee7; border-radius: .9rem; background: #f7faf8; }
    .cotec-nav-dropdown { position: relative; }
    .cotec-nav-dropdown > summary { display: flex; min-height: 40px; align-items: center; gap: .55rem; padding: 0 .9rem; border: 1px solid transparent; border-radius: .65rem; color: #334155; cursor: pointer; font-size: .84rem; font-weight: 700; list-style: none; white-space: nowrap; transition: background-color .16s ease, border-color .16s ease, color .16s ease, box-shadow .16s ease; }
    .cotec-nav-dropdown > summary::-webkit-details-marker { display: none; }
    .cotec-nav-dropdown > summary::marker { content: ""; }
    .cotec-nav-dropdown > summary .cotec-nav-icon { color: #15803d; font-size: .78rem; }
    .cotec-nav-dropdown > summary .cotec-nav-chevron { color: #94a3b8; font-size: .62rem; transition: transform .16s ease; }
    .cotec-nav-dropdown:hover > summary,
    .cotec-nav-dropdown:focus-within > summary,
    .cotec-nav-dropdown[open] > summary { border-color: #bbf7d0; background: #fff; color: #14532d; box-shadow: 0 2px 6px rgba(20,83,45,.08); }
    .cotec-nav-dropdown[open] > summary .cotec-nav-chevron { transform: rotate(180deg); }
    .cotec-nav-panel { position: absolute; z-index: 100; top: calc(100% + .4rem); left: 50%; width: 260px; max-width: calc(100vw - 1.5rem); padding-top: .45rem; opacity: 0; pointer-events: none; transform: translate(-50%, .35rem); transition: opacity .14s ease, transform .14s ease, visibility .14s ease; visibility: hidden; }
    .cotec-nav-panel::before { position: absolute; right: 0; bottom: 100%; left: 0; height: .5rem; content: ""; }
    .cotec-nav-dropdown:hover > .cotec-nav-panel,
    .cotec-nav-dropdown:focus-within > .cotec-nav-panel,
    .cotec-nav-dropdown[open] > .cotec-nav-panel { opacity: 1; pointer-events: auto; transform: translate(-50%, 0); visibility: visible; }
    .cotec-nav-panel ul { margin: 0; padding: .35rem; border: 1px solid #e2e8f0; border-radius: .85rem; background: #fff; box-shadow: 0 14px 32px rgba(15,23,42,.14); list-style: none; }
    .cotec-nav-panel a { display: flex; align-items: center; gap: .7rem; min-height: 40px; padding: .55rem .7rem; border-radius: .55rem; color: #475569; font-size: .84rem; font-weight: 600; text-decoration: none; transition: background-color .14s ease, color .14s ease, padding .14s ease; }
    .cotec-nav-panel a i { width: 1rem; color: #15803d; text-align: center; }
    .cotec-nav-panel a:hover,
    .cotec-nav-panel a:focus-visible { padding-left: .9rem; background: #f0fdf4; color: #14532d; outline: none; }
    .cotec-nav-panel a.active,
    .cotec-nav-panel a[class*="bg-green-50"] { background: #ecfdf5; color: #14532d; }
    .cotec-user-area { grid-column: 3; grid-row: 1; display: flex; min-width: 0; align-items: center; justify-self: end; gap: .65rem; }
    .cotec-user-avatar { display: flex; width: 40px; height: 40px; flex: 0 0 40px; align-items: center; justify-content: center; border: 1px solid #bbf7d0; border-radius: 50%; background: #dcfce7; color: #14532d; font-size: .72rem; font-weight: 800; }
    .cotec-user-info { max-width: 150px; min-width: 0; text-align: right; }
    .cotec-user-name { overflow: hidden; color: #0f172a; font-size: .82rem; font-weight: 700; text-overflow: ellipsis; white-space: nowrap; }
    .cotec-user-role { color: #64748b; font-size: .59rem; letter-spacing: .15em; text-transform: uppercase; }
    .cotec-topbar-action { display: inline-flex; min-height: 36px; align-items: center; justify-content: center; padding: 0 .7rem; border: 1px solid #e2e8f0; border-radius: .6rem; background: #fff; color: #475569; cursor: pointer; font-size: .72rem; font-weight: 700; text-decoration: none; white-space: nowrap; transition: border-color .15s ease, background .15s ease, color .15s ease; }
    .cotec-topbar-action:hover { border-color: #86efac; background: #f0fdf4; color: #14532d; }
    .cotec-topbar-action--primary { border-color: #14532d; background: #14532d; color: #fff; }
    .cotec-topbar-action--primary:hover { border-color: #166534; background: #166534; color: #fff; }
    @media (max-width: 900px) {
        .cotec-topbar-inner { grid-template-columns: minmax(0,1fr) auto; gap: .6rem; padding: .65rem 1rem; }
        .cotec-topbar-groups { grid-column: 1 / -1; grid-row: 2; justify-self: center; }
        .cotec-user-area, .cotec-guest-area { grid-column: 2; grid-row: 1; }
    }
    @media (max-width: 520px) {
        .cotec-topbar-inner { padding: .55rem .65rem; }
        .cotec-brand { gap: .45rem; }
        .cotec-brand-logo { width: 36px; height: 36px; flex-basis: 36px; }
        .cotec-brand-name { padding-left: .5rem; }
        .cotec-brand-name span:last-child { font-size: .78rem; }
        .cotec-topbar-groups { width: 100%; justify-content: space-around; }
        .cotec-nav-dropdown > summary { min-height: 38px; gap: .35rem; padding: 0 .5rem; font-size: .73rem; }
        .cotec-user-info, .cotec-topbar-action--logout { display: none; }
        .cotec-user-avatar { width: 34px; height: 34px; flex-basis: 34px; }
    }
    @media (prefers-reduced-motion: reduce) {
        .cotec-nav-dropdown > summary, .cotec-nav-panel, .cotec-nav-panel a, .cotec-topbar-action { transition: none; }
    }
</style>

<nav class="cotec-topbar" x-data="{}">
    <div class="cotec-topbar-inner">
        <div class="cotec-brand">
            {{-- Con sesión: al panel (los usuarios finales son redirigidos al catálogo). Sin sesión: a la página de inicio. --}}
            <a href="{{ auth()->check() ? route('dashboard') : route('welcome') }}" class="cotec-brand-logo" aria-label="Inicio">
                @if (file_exists(public_path('build/images/logo-cotecnova.png')))
                    <img src="{{ asset('build/images/logo-cotecnova.png') }}" alt="Logo COTECNOVA" class="h-full w-full object-contain" />
                @elseif (file_exists(public_path('build/images/logo-cotecnova.svg')))
                    <img src="{{ asset('build/images/logo-cotecnova.svg') }}" alt="Logo COTECNOVA" class="h-full w-full object-contain" />
                @else
                    <x-application-logo class="h-full w-full" />
                @endif
            </a>

            <div class="cotec-brand-name">
                <span>Biblioteca</span>
                <span>COTECNOVA</span>
            </div>
        </div>

        <div class="cotec-topbar-groups">
                <details class="cotec-nav-dropdown" @click.outside="$el.open = false" @keydown.escape="$el.open = false">
                    <summary class="cotec-nav-trigger">
                        <i class="cotec-nav-icon fa-solid fa-gears" aria-hidden="true"></i>
                        <span>Operaciones</span>
                        <i class="cotec-nav-chevron fa-solid fa-chevron-down" aria-hidden="true"></i>
                    </summary>
                    <nav aria-label="Operaciones" class="cotec-nav-panel">
                        <div class="max-h-[75vh] overflow-y-auto">
                        <ul class="list-none space-y-0.5 p-0">
                            @auth
                                @if (Auth::user()->esAdministrador())
                                    <li><a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-green-50 text-green-900' : 'text-slate-700 hover:bg-slate-50' }}"><i class="fa-solid fa-gauge-high w-4 text-center" aria-hidden="true"></i>Panel de control</a></li>
                                    <li><a href="{{ route('libros.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('libros.*', 'ejemplares.*') ? 'bg-green-50 text-green-900' : 'text-slate-700 hover:bg-slate-50' }}"><i class="fa-solid fa-book-open-reader w-4 text-center" aria-hidden="true"></i>Libros y ejemplares</a></li>
                                    <li><a href="{{ route('importaciones.catalogo') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('importaciones.catalogo*') ? 'bg-green-50 text-green-900' : 'text-slate-700 hover:bg-slate-50' }}"><i class="fa-solid fa-file-import w-4 text-center" aria-hidden="true"></i>Importar catálogo</a></li>
                                    <li><a href="{{ route('prestamos.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('prestamos.index', 'prestamos.create') ? 'bg-green-50 text-green-900' : 'text-slate-700 hover:bg-slate-50' }}"><i class="fa-solid fa-hand-holding-hand w-4 text-center" aria-hidden="true"></i>Préstamos</a></li>
                                    <li><a href="{{ route('reservas.todas') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('reservas.todas') ? 'bg-green-50 text-green-900' : 'text-slate-700 hover:bg-slate-50' }}"><i class="fa-solid fa-bookmark w-4 text-center" aria-hidden="true"></i>Reservas</a></li>
                                    <li><a href="{{ route('adquisiciones.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('adquisiciones.*', 'proveedores.*') ? 'bg-green-50 text-green-900' : 'text-slate-700 hover:bg-slate-50' }}"><i class="fa-solid fa-cart-flatbed w-4 text-center" aria-hidden="true"></i>Adquisiciones y proveedores</a></li>
                                    <li><a href="{{ route('usuarios.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('usuarios.index', 'usuarios.update') ? 'bg-green-50 text-green-900' : 'text-slate-700 hover:bg-slate-50' }}"><i class="fa-solid fa-users w-4 text-center" aria-hidden="true"></i>Usuarios</a></li>
                                    <li><a href="{{ route('usuarios.historial') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('usuarios.historial') ? 'bg-green-50 text-green-900' : 'text-slate-700 hover:bg-slate-50' }}"><i class="fa-solid fa-clock-rotate-left w-4 text-center" aria-hidden="true"></i>Historial por cédula</a></li>
                                @else
                                    <li><a href="{{ route('catalogo.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('catalogo.*') ? 'bg-green-50 text-green-900' : 'text-slate-700 hover:bg-slate-50' }}"><i class="fa-solid fa-magnifying-glass w-4 text-center" aria-hidden="true"></i>Consultar catálogo</a></li>
                                    <li><a href="{{ route('reservas.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('reservas.index') ? 'bg-green-50 text-green-900' : 'text-slate-700 hover:bg-slate-50' }}"><i class="fa-solid fa-bookmark w-4 text-center" aria-hidden="true"></i>Mis reservas</a></li>
                                    <li><a href="{{ route('prestamos.mios') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('prestamos.mios') ? 'bg-green-50 text-green-900' : 'text-slate-700 hover:bg-slate-50' }}"><i class="fa-solid fa-clock-rotate-left w-4 text-center" aria-hidden="true"></i>Mis préstamos</a></li>
                                @endif
                            @else
                                <li><a href="{{ route('catalogo.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('catalogo.*') ? 'bg-green-50 text-green-900' : 'text-slate-700 hover:bg-slate-50' }}"><i class="fa-solid fa-magnifying-glass w-4 text-center" aria-hidden="true"></i>Consultar catálogo</a></li>
                            @endauth
                        </ul>
                        </div>
                    </nav>
                </details>

                @auth
                    <details class="cotec-nav-dropdown" @click.outside="$el.open = false" @keydown.escape="$el.open = false">
                        <summary class="cotec-nav-trigger">
                            <i class="cotec-nav-icon fa-solid fa-sliders" aria-hidden="true"></i>
                            <span>Configuración</span>
                            <i class="cotec-nav-chevron fa-solid fa-chevron-down" aria-hidden="true"></i>
                        </summary>
                        <nav aria-label="Configuración" class="cotec-nav-panel">
                            <ul class="list-none space-y-0.5 rounded-xl border border-slate-200 bg-white p-2 text-left shadow-lg shadow-slate-900/10">
                                @if (Auth::user()->esAdministrador())
                                    <li><a href="{{ route('ubicaciones.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('ubicaciones.*') ? 'bg-green-50 text-green-900' : 'text-slate-700 hover:bg-slate-50' }}"><i class="fa-solid fa-location-dot w-4 text-center" aria-hidden="true"></i>Ubicaciones</a></li>
                                @endif
                                <li><a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('profile.*') ? 'bg-green-50 text-green-900' : 'text-slate-700 hover:bg-slate-50' }}"><i class="fa-solid fa-user w-4 text-center" aria-hidden="true"></i>Mi perfil</a></li>
                            </ul>
                        </nav>
                    </details>
                @endauth

                @auth
                    @if (Auth::user()->esAdministrador())
                        <details class="cotec-nav-dropdown" @click.outside="$el.open = false" @keydown.escape="$el.open = false">
                            <summary class="cotec-nav-trigger">
                                <i class="cotec-nav-icon fa-solid fa-chart-column" aria-hidden="true"></i>
                                <span>Reportes</span>
                                <i class="cotec-nav-chevron fa-solid fa-chevron-down" aria-hidden="true"></i>
                            </summary>
                            <nav aria-label="Reportes" class="cotec-nav-panel">
                                <ul class="list-none space-y-0.5 rounded-xl border border-slate-200 bg-white p-2 text-left shadow-lg shadow-slate-900/10">
                                    <li><a href="{{ route('reportes.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('reportes.*') ? 'bg-green-50 text-green-900' : 'text-slate-700 hover:bg-slate-50' }}"><i class="fa-solid fa-file-lines w-4 text-center" aria-hidden="true"></i>Descargar reportes</a></li>
                                    <li><a href="{{ route('inventory.kardex') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('inventory.kardex') ? 'bg-green-50 text-green-900' : 'text-slate-700 hover:bg-slate-50' }}"><i class="fa-solid fa-list w-4 text-center" aria-hidden="true"></i>Kardex de inventario</a></li>
                                    <li><a href="{{ route('inventory.adjust') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('inventory.adjust*') ? 'bg-green-50 text-green-900' : 'text-slate-700 hover:bg-slate-50' }}"><i class="fa-solid fa-boxes-stacked w-4 text-center" aria-hidden="true"></i>Ajustes de inventario</a></li>
                                    <li><a href="{{ route('audit.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('audit.*') ? 'bg-green-50 text-green-900' : 'text-slate-700 hover:bg-slate-50' }}"><i class="fa-solid fa-clipboard-check w-4 text-center" aria-hidden="true"></i>Auditoría</a></li>
                                </ul>
                            </nav>
                        </details>
                    @endif
                @endauth
        </div>

        @auth
            <div class="cotec-user-area">
                <div class="cotec-user-avatar">
                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                </div>

                <div class="cotec-user-info">
                    <div class="cotec-user-name">{{ Auth::user()->name }}</div>
                    <div class="cotec-user-role">
                        {{ Auth::user()->esAdministrador() ? 'Administrador' : 'Usuario' }}
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="cotec-topbar-action cotec-topbar-action--logout">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        @endauth

        {{-- Visitantes: como el catálogo es público, necesitan cómo entrar --}}
        @guest
            <div class="cotec-user-area cotec-guest-area">
                <a href="{{ route('login') }}" class="cotec-topbar-action">
                    Iniciar sesión
                </a>
                <a href="{{ route('register') }}" class="cotec-topbar-action cotec-topbar-action--primary">
                    Registrarme
                </a>
            </div>
        @endguest
    </div>
</nav>