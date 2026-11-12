<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Biblioteca COTECNOVA') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

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

            .cotec-app {
                display: flex;
                min-height: 100vh;
                flex-direction: column;
                background: #edf8f1;
                color: #1e293b;
            }

            .cotec-shell {
                display: flex;
                flex: 1;
                flex-direction: column;
                width: 100%;
                max-width: 1500px;
                margin: 0 auto;
                padding: 1.25rem 1rem 2rem;
            }

            .cotec-layout {
                display: block;
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
                margin-top: auto;
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

            @media (max-width: 640px) {
                .cotec-shell { padding: 0.75rem 0.65rem 1.25rem; }
                .cotec-main { padding-top: 0.65rem; }
                .cotec-footer-inner { flex-direction: column; padding: 1.25rem; }
                .cotec-panel { border-radius: 1rem; }
            }

            @media (prefers-reduced-motion: reduce) {
                summary { transition: none !important; }
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="cotec-app">
            @include('layouts.navigation')

            <div class="cotec-shell">
                <div class="cotec-layout">
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
                        <div>Versión Prototipo</div>
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