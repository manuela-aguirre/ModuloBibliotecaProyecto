<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Biblioteca COTECNOVA') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwUEQp5WJb4JjY0pwW2B2Qz8KCA7vVqQxL5ZQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            :root {
                --cotec-green-900: #14532d;
                --cotec-green-800: #166534;
                --cotec-green-700: #15803d;
                --cotec-green-50: #f0fdf4;
                --cotec-green-100: #dcfce7;
                --cotec-slate-900: #0f172a;
                --cotec-slate-500: #64748b;
                --cotec-border: rgba(20, 83, 45, 0.12);
            }

            body {
                font-family: 'Figtree', sans-serif;
                background: linear-gradient(180deg, #edf9f0 0%, #f8fafc 100%);
            }

            .cotec-shell {
                max-width: 1500px;
                margin: 0 auto;
                padding: 1.25rem 1rem 2rem;
            }

            .cotec-layout {
                display: grid;
                grid-template-columns: 260px minmax(0, 1fr);
                gap: 1.25rem;
            }

            .cotec-sidebar {
                background: linear-gradient(180deg, #14532d 0%, #0f3f29 100%);
                color: white;
                border-radius: 1.25rem;
                padding: 1.25rem 1rem;
                box-shadow: 0 18px 35px rgba(20, 83, 45, 0.15);
            }

            .cotec-sidebar-header {
                display: flex;
                align-items: center;
                gap: 0.8rem;
                padding: 0.35rem 0.45rem 1rem;
                border-bottom: 1px solid rgba(255,255,255,0.15);
                margin-bottom: 1rem;
            }

            .cotec-sidebar-logo {
                width: 54px;
                height: 54px;
                border-radius: 0;
                background: transparent;
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
                flex-shrink: 0;
            }

            .cotec-sidebar-logo img {
                width: 100%;
                height: 100%;
                object-fit: contain;
                display: block;
            }

            .cotec-sidebar-brand small {
                display: block;
                letter-spacing: 0.18em;
                text-transform: uppercase;
                font-size: 0.58rem;
                opacity: 0.8;
            }

            .cotec-sidebar-brand strong {
                display: block;
                font-size: 0.95rem;
                line-height: 1.2;
            }

            .cotec-section-label {
                margin: 0.8rem 0 0.4rem;
                padding: 0 0.4rem;
                color: #d1fae5;
                font-size: 0.68rem;
                letter-spacing: 0.14em;
                text-transform: uppercase;
                font-weight: 700;
            }

            .cotec-section-toggle {
                width: 100%;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 0.6rem;
                padding: 0.7rem 0.7rem;
                border: 1px solid rgba(255,255,255,0.08);
                border-radius: 0.8rem;
                background: rgba(255,255,255,0.04);
                color: #f0fdf4;
                font-weight: 700;
                letter-spacing: 0.02em;
                text-transform: none;
                text-align: left;
                cursor: pointer;
                transition: background 0.2s ease;
            }

            .cotec-section-toggle:hover {
                background: rgba(255,255,255,0.08);
            }

            .cotec-nav-group {
                display: grid;
                gap: 0.4rem;
            }

            .cotec-side-link {
                display: flex;
                align-items: center;
                gap: 0.8rem;
                width: 100%;
                border: 1px solid transparent;
                background: rgba(255,255,255,0.04);
                color: rgba(255,255,255,0.96);
                text-decoration: none;
                padding: 0.8rem 0.8rem;
                border-radius: 0.8rem;
                transition: all 0.2s ease;
                font-weight: 600;
            }

            .cotec-side-link:hover,
            .cotec-side-link.active {
                background: rgba(255,255,255,0.1);
                border-color: rgba(255,255,255,0.14);
                transform: translateX(2px);
            }

            .cotec-side-link i {
                width: 1.1rem;
                text-align: center;
                opacity: 0.95;
            }

            .cotec-content {
                min-width: 0;
            }

            .cotec-panel {
                background: white;
                border-radius: 1.2rem;
                border: 1px solid var(--cotec-border);
                box-shadow: 0 10px 25px rgba(15, 23, 42, 0.04);
            }

            .cotec-main {
                padding: 1rem 0 0;
            }

            .cotec-footer {
                margin-top: 1.5rem;
                background: #0f172a;
                color: rgba(255,255,255,0.82);
                border-top: 1px solid rgba(255,255,255,0.08);
            }

            .cotec-footer-inner {
                max-width: 1500px;
                margin: 0 auto;
                padding: 1.5rem 1rem;
                display: flex;
                justify-content: space-between;
                gap: 1rem;
                flex-wrap: wrap;
            }

            .cotec-footer-brand {
                display: flex;
                align-items: center;
                gap: 0.7rem;
            }

            .cotec-footer-brand img {
                width: 36px;
                height: 36px;
                border-radius: 50%;
                object-fit: contain;
                background: white;
            }

            @media (max-width: 1024px) {
                .cotec-layout {
                    grid-template-columns: 1fr;
                }

                .cotec-sidebar {
                    order: 2;
                }
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-[#edf8f1] text-slate-800">
            @include('layouts.navigation')

            <div class="cotec-shell">
                <div class="cotec-layout">
                    <aside class="cotec-sidebar" x-data="{ openOperaciones: true, openConfiguracion: false, openReportes: false }">

                        {{-- ===================== OPERACIONES ===================== --}}
                        <div class="cotec-section-label">
                            <button type="button" @click="openOperaciones = !openOperaciones" class="cotec-section-toggle">
                                <span>Operaciones</span>
                                <i class="fa-solid" :class="openOperaciones ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
                            </button>
                        </div>
                        <nav x-show="openOperaciones" x-transition class="cotec-nav-group">
                            {{-- Solo personal de la biblioteca (administrador) --}}
                            @if (auth()->user()?->esAdministrador())
                                <a href="{{ route('dashboard') }}" class="cotec-side-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
                                <a href="{{ route('libros.index') }}" class="cotec-side-link {{ request()->routeIs('libros.*', 'ejemplares.*') ? 'active' : '' }}"><i class="fa-solid fa-book-open-reader"></i> Libros y ejemplares</a>
                                <a href="{{ route('prestamos.index') }}" class="cotec-side-link {{ request()->routeIs('prestamos.index', 'prestamos.create') ? 'active' : '' }}"><i class="fa-solid fa-hand-holding-hand"></i> Préstamos</a>
                                <a href="{{ route('reservas.todas') }}" class="cotec-side-link {{ request()->routeIs('reservas.todas') ? 'active' : '' }}"><i class="fa-solid fa-file-lines"></i> Reservas</a>
                                <a href="{{ route('usuarios.index') }}" class="cotec-side-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}"><i class="fa-solid fa-user-group"></i> Usuarios</a>
                            @endif

                            {{-- Visible para todos, incluso sin iniciar sesión --}}
                            <a href="{{ route('catalogo.index') }}" class="cotec-side-link {{ request()->routeIs('catalogo.*') ? 'active' : '' }}"><i class="fa-solid fa-magnifying-glass"></i> Consultar disponibilidad</a>

                            {{-- Solo usuarios finales (estudiantes, docentes, administrativos) --}}
                            @auth
                                @unless (auth()->user()->esAdministrador())
                                    <a href="{{ route('reservas.index') }}" class="cotec-side-link {{ request()->routeIs('reservas.index') ? 'active' : '' }}"><i class="fa-solid fa-bookmark"></i> Mis reservas</a>
                                    <a href="{{ route('prestamos.mios') }}" class="cotec-side-link {{ request()->routeIs('prestamos.mios') ? 'active' : '' }}"><i class="fa-solid fa-clock-rotate-left"></i> Mis préstamos</a>
                                @endunless
                            @endauth
                        </nav>

                        {{-- ===================== CONFIGURACIÓN ===================== --}}
                        <div class="cotec-section-label">
                            <button type="button" @click="openConfiguracion = !openConfiguracion" class="cotec-section-toggle">
                                <span>Configuración</span>
                                <i class="fa-solid" :class="openConfiguracion ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
                            </button>
                        </div>
                        <nav x-show="openConfiguracion" x-transition class="cotec-nav-group">
                            @if (auth()->user()?->esAdministrador())
                                <a href="{{ route('ubicaciones.index') }}" class="cotec-side-link {{ request()->routeIs('ubicaciones.*') ? 'active' : '' }}"><i class="fa-solid fa-location-dot"></i> Ubicaciones</a>
                            @endif
                            @auth
                                <a href="{{ route('profile.edit') }}" class="cotec-side-link {{ request()->routeIs('profile.*') ? 'active' : '' }}"><i class="fa-solid fa-user"></i> Perfil</a>
                            @endauth
                        </nav>

                        {{-- ===================== REPORTES (solo administrador) ===================== --}}
                        @if (auth()->user()?->esAdministrador())
                            <div class="cotec-section-label">
                                <button type="button" @click="openReportes = !openReportes" class="cotec-section-toggle">
                                    <span>Reportes</span>
                                    <i class="fa-solid" :class="openReportes ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
                                </button>
                            </div>
                            <nav x-show="openReportes" x-transition class="cotec-nav-group">
                                <a href="{{ route('reportes.prestamos') }}" class="cotec-side-link"><i class="fa-solid fa-file-csv"></i> Préstamos (CSV)</a>
                                <a href="{{ route('reportes.inventario') }}" class="cotec-side-link"><i class="fa-solid fa-boxes-stacked"></i> Inventario (CSV)</a>
                            </nav>
                        @endif
                    </aside>

                    <div class="cotec-content">
                        @isset($header)
                            <header class="cotec-panel bg-white shadow-sm">
                                <div class="max-w-7xl mx-auto py-5 px-5 sm:px-6 lg:px-8">
                                    {{ $header }}
                                </div>
                            </header>
                        @endisset

                        <main class="cotec-main">
                            {{ $slot }}
                        </main>
                    </div>
                </div>
            </div>

            <footer class="cotec-footer">
                <div class="cotec-footer-inner">
                    <div>
                        <div style="font-size:0.7rem;letter-spacing:0.16em;text-transform:uppercase;opacity:0.7;margin-bottom:0.5rem;">Sistema</div>
                        <div>Versión 2.4.0</div>
                    </div>

                    <div>
                        <div style="font-size:0.7rem;letter-spacing:0.16em;text-transform:uppercase;opacity:0.7;margin-bottom:0.5rem;">Contacto</div>
                        <div>biblioteca@cotecnova.edu.co</div>
                    </div>

                    <div>
                        <div style="font-size:0.7rem;letter-spacing:0.16em;text-transform:uppercase;opacity:0.7;margin-bottom:0.5rem;">© {{ date('Y') }}</div>
                        <div>Todos los derechos reservados</div>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>