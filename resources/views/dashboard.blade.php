<x-app-layout>
    <x-slot name="header">
        <h2 class="cotec-header-title">
            Panel de Control
        </h2>
    </x-slot>

    <style>
        .cotec-header-title {
            font-family: 'Figtree', Arial, sans-serif;
            font-weight: 600;
            font-size: 1.25rem;
            color: #14532d;
        }

        .cotec-dash-wrap {
            padding: 1.25rem 0;
            font-family: 'Figtree', Arial, sans-serif;
        }

        .cotec-dash-inner {
            max-width: 1240px;
            margin: 0 auto;
        }

        .cotec-welcome-card {
            background: linear-gradient(90deg, #14532d 0%, #16a34a 100%);
            border-radius: 1rem;
            padding: 2rem;
            color: #ffffff;
            box-shadow: 0 10px 25px rgba(0,0,0,0.12);
            margin-bottom: 1.25rem;
        }

        .cotec-dashboard-brand {
            display: flex;
            align-items: center;
            gap: 0.9rem;
            margin-bottom: 1rem;
        }

        .cotec-dashboard-logo {
            width: 58px;
            height: 58px;
            background: #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 20px rgba(11, 30, 19, 0.2);
            border: 4px solid rgba(255,255,255,0.45);
            overflow: hidden;
            flex-shrink: 0;
        }

        .cotec-dashboard-logo svg {
            width: 100%;
            height: 100%;
            display: block;
        }

        .cotec-dashboard-brand small {
            display: block;
            font-size: 0.66rem;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            opacity: 0.8;
            color: #dcfce7;
        }

        .cotec-welcome-card h1 {
            margin: 0;
            font-size: 1.6rem;
            font-weight: 700;
        }

        .cotec-welcome-card p {
            margin: 0.35rem 0 0 0;
            font-size: 0.95rem;
            color: #dcfce7;
        }

        .cotec-kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1rem;
            align-items: stretch;
        }

        .cotec-dashboard-section-heading {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 0.5rem 1rem;
            flex-wrap: wrap;
            margin: 1.5rem 0 0.75rem;
        }

        .cotec-dashboard-section-heading h2 {
            margin: 0;
            color: #14532d;
            font-size: 1rem;
            font-weight: 700;
        }

        .cotec-dashboard-section-heading p {
            margin: 0;
            color: #64748b;
            font-size: 0.8rem;
        }

        @media (max-width: 1100px) {
            .cotec-kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 520px) {
            .cotec-kpi-grid { grid-template-columns: 1fr; }
            .cotec-welcome-card { padding: 1.4rem; }
            .cotec-welcome-card h1 { font-size: 1.35rem; }
        }

        .cotec-kpi-card {
            position: relative;
            min-height: 250px;
            background: #ffffff;
            border: 1px solid rgba(20, 83, 45, 0.12);
            border-radius: 1rem;
            padding: 1.25rem;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
            transition: box-shadow 0.2s, transform 0.2s;
            overflow: visible;
        }

        .cotec-kpi-card:hover {
            box-shadow: 0 8px 18px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .cotec-kpi-card:focus-within { outline: 3px solid rgba(21, 128, 61, 0.18); outline-offset: 2px; }

        .cotec-kpi-tooltip {
            position: absolute;
            left: 50%;
            bottom: calc(100% + 10px);
            transform: translateX(-50%) translateY(6px);
            opacity: 0;
            width: 220px;
            max-width: calc(100vw - 2rem);
            padding: 0.7rem 0.8rem;
            background: rgba(15, 23, 42, 0.95);
            color: #f8fafc;
            border-radius: 0.7rem;
            font-size: 0.72rem;
            line-height: 1.45;
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.18);
            pointer-events: none;
            transition: opacity 0.18s ease, transform 0.18s ease;
            z-index: 20;
        }

        .cotec-kpi-card:hover .cotec-kpi-tooltip,
        .cotec-kpi-card:focus-within .cotec-kpi-tooltip {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        .cotec-kpi-tooltip::after {
            content: "";
            position: absolute;
            left: 50%;
            top: 100%;
            width: 10px;
            height: 10px;
            background: rgba(15, 23, 42, 0.95);
            transform: translateX(-50%) rotate(45deg);
            border-radius: 2px;
        }

        .cotec-kpi-icon {
            width: 44px;
            height: 44px;
            flex: 0 0 44px;
            border-radius: 0.65rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .cotec-kpi-icon.green { background: #dcfce7; }
        .cotec-kpi-icon.blue { background: #dbeafe; }
        .cotec-kpi-icon.amber { background: #fef3c7; }
        .cotec-kpi-icon.red { background: #fee2e2; }
        .cotec-kpi-icon.green i { color: #15803d; }
        .cotec-kpi-icon.blue i { color: #2563eb; }
        .cotec-kpi-icon.amber i { color: #b45309; }
        .cotec-kpi-icon.red i { color: #dc2626; }

        .cotec-kpi-value {
            font-size: clamp(1.7rem, 2vw, 2.15rem);
            line-height: 1.05;
            font-weight: 700;
            color: #111827;
            letter-spacing: -0.035em;
            font-variant-numeric: tabular-nums;
        }

        .cotec-kpi-label {
            font-size: 0.85rem;
            color: #334155;
            font-weight: 600;
        }

        .cotec-kpi-card.alert .cotec-kpi-value {
            color: #b91c1c;
        }

        .cotec-kpi-card.alert {
            border-color: #fecaca;
            border-left: 4px solid #ef4444;
            background: linear-gradient(180deg, #fff 0%, #fffafa 100%);
        }

        .cotec-kpi-card.healthy .cotec-kpi-value { color: #166534; }

        .cotec-kpi-action {
            margin-top: auto;
            color: #14532d;
            font-size: 0.78rem;
            font-weight: 700;
            text-decoration: none;
        }

        .cotec-kpi-action:hover { text-decoration: underline; text-underline-offset: 3px; }
        .cotec-kpi-action:focus-visible { outline: 2px solid #15803d; outline-offset: 3px; border-radius: 0.2rem; }
        .cotec-kpi-detail { min-height: 2.8em; color: #64748b; font-size: 0.78rem; line-height: 1.4; }

        .cotec-dashboard-panels {
            margin-top: 1.5rem;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .cotec-dashboard-panel {
            min-width: 0;
            border: 1px solid rgba(20, 83, 45, 0.12);
            border-radius: 1rem;
            background: #fff;
            padding: 1.1rem;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
        }

        .cotec-dashboard-panel h3 {
            margin: 0;
            color: #14532d;
            font-size: 0.95rem;
            font-weight: 700;
            line-height: 1.3;
        }

        .cotec-dashboard-panel-intro { margin: 0.35rem 0 0; color: #64748b; font-size: 0.78rem; }
        .cotec-dashboard-bar-row { margin-top: 0.85rem; padding-bottom: 0.7rem; border-bottom: 1px solid #f1f5f9; }
        .cotec-dashboard-bar-row:last-child { padding-bottom: 0; border-bottom: 0; }
        .cotec-dashboard-bar-label { display: flex; justify-content: space-between; gap: 0.75rem; color: #334155; font-size: 0.84rem; }
        .cotec-dashboard-bar-label span { min-width: 0; overflow-wrap: anywhere; }
        .cotec-dashboard-bar-label strong { color: #0f172a; font-variant-numeric: tabular-nums; }
        .cotec-dashboard-bar-track { margin-top: 0.4rem; height: 0.55rem; overflow: hidden; border-radius: 999px; background: #f1f5f9; }
        .cotec-dashboard-bar { height: 100%; border-radius: inherit; background: #15803d; }
        .cotec-dashboard-bar.blue { background: #2563eb; }
        .cotec-dashboard-bar.amber { background: #f59e0b; }

        @media (max-width: 720px) {
            .cotec-dashboard-panels { grid-template-columns: 1fr; }
            .cotec-kpi-card { padding: 1rem; }
        }

        .cotec-book-form-card {
            margin-top: 2rem;
            background: #ffffff;
            border: 1px solid #dfeee3;
            border-radius: 1.2rem;
            box-shadow: 0 14px 30px rgba(20, 83, 45, 0.08);
            overflow: hidden;
        }

        .cotec-book-form-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            background: linear-gradient(90deg, #f0fdf4 0%, #ecfdf5 100%);
            border-bottom: 1px solid #d1fae5;
            padding: 1.3rem 1.5rem;
        }

        .cotec-book-form-title {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            color: #14532d;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .cotec-book-form-icon {
            width: 42px;
            height: 42px;
            border-radius: 0.8rem;
            background: #14532d;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 18px rgba(20, 83, 45, 0.15);
        }

        .cotec-book-form-body {
            padding: 1.5rem;
        }

        .cotec-book-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem 1.2rem;
        }

        .cotec-field {
            display: flex;
            flex-direction: column;
            gap: 0.45rem;
        }

        .cotec-field label {
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #14532d;
        }

        .cotec-input,
        .cotec-select,
        .cotec-textarea {
            width: 100%;
            border: 1px solid #d1fae5;
            background: #f8fffb;
            color: #0f172a;
            border-radius: 0.8rem;
            padding: 0.8rem 0.9rem;
            font-size: 0.95rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .cotec-input:focus,
        .cotec-select:focus,
        .cotec-textarea:focus {
            outline: none;
            border-color: #22c55e;
            box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.12);
        }

        .cotec-textarea {
            resize: vertical;
            min-height: 110px;
        }

        .cotec-form-actions {
            margin-top: 1.5rem;
            display: flex;
            justify-content: flex-end;
            gap: 0.8rem;
            flex-wrap: wrap;
        }

        .cotec-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border-radius: 999px;
            padding: 0.8rem 1.3rem;
            font-weight: 700;
            border: 1px solid transparent;
            cursor: pointer;
            text-decoration: none;
        }

        .cotec-btn-light {
            background: #ecfdf5;
            color: #14532d;
            border-color: #bbf7d0;
        }

        .cotec-btn-dark {
            background: linear-gradient(90deg, #14532d 0%, #15803d 100%);
            color: white;
            box-shadow: 0 10px 20px rgba(20, 83, 45, 0.15);
        }

        @media (max-width: 720px) {
            .cotec-book-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="cotec-dash-wrap">
        <div class="cotec-dash-inner">

            <!-- Bienvenida -->
            <div class="cotec-welcome-card">
                <h1>¡Bienvenido, {{ auth()->user()->name }}!</h1>
                <p>Biblioteca COTECNOVA - Panel de Control</p>
            </div>

            <!-- KPIs -->
            <div class="cotec-dashboard-section-heading">
                <h2>Resumen general</h2>
                <p>Indicadores y alertas de operación de la biblioteca</p>
            </div>
            <section class="cotec-kpi-grid" aria-label="Resumen y alertas de la biblioteca">
                <article class="cotec-kpi-card {{ $prestamosVencidos > 0 ? 'alert' : 'healthy' }}">
                    <div class="cotec-kpi-icon {{ $prestamosVencidos > 0 ? 'red' : 'green' }}"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i></div>
                    <div class="cotec-kpi-value">{{ $prestamosVencidos }}</div>
                    <div class="cotec-kpi-label">Préstamos vencidos</div>
                    <div class="cotec-kpi-detail">Tasa de vencimiento: {{ $tasaVencimiento }}% de los préstamos activos.</div>
                    <a class="cotec-kpi-action" href="{{ route('prestamos.index', ['estado' => 'vencidos']) }}">Revisar vencidos <span aria-hidden="true">→</span></a>
                </article>

                <article class="cotec-kpi-card {{ $multasPendientes > 0 ? 'alert' : 'healthy' }}">
                    <div class="cotec-kpi-icon {{ $multasPendientes > 0 ? 'red' : 'green' }}"><i class="fa-solid fa-coins" aria-hidden="true"></i></div>
                    <div class="cotec-kpi-value">${{ number_format($multasPendientes, 0, ',', '.') }}</div>
                    <div class="cotec-kpi-label">Multas pendientes</div>
                    <div class="cotec-kpi-detail">Incluye multas registradas y acumuladas de préstamos vencidos.</div>
                    <a class="cotec-kpi-action" href="{{ route('prestamos.index') }}">Gestionar préstamos <span aria-hidden="true">→</span></a>
                </article>

                <article class="cotec-kpi-card {{ $librosBajoStock > 0 ? 'alert' : 'healthy' }}">
                    <div class="cotec-kpi-icon {{ $librosBajoStock > 0 ? 'red' : 'green' }}"><i class="fa-solid fa-boxes-stacked" aria-hidden="true"></i></div>
                    <div class="cotec-kpi-value">{{ $librosBajoStock }}</div>
                    <div class="cotec-kpi-label">Títulos con stock crítico</div>
                    <div class="cotec-kpi-detail">Dos o menos ejemplares disponibles.</div>
                    <a class="cotec-kpi-action" href="{{ route('libros.index') }}">Revisar catálogo <span aria-hidden="true">→</span></a>
                </article>

                <article class="cotec-kpi-card">
                    <div class="cotec-kpi-icon amber"><i class="fa-solid fa-book-open" aria-hidden="true"></i></div>
                    <div class="cotec-kpi-value">{{ $prestamosActivos }}</div>
                    <div class="cotec-kpi-label">Préstamos activos</div>
                    <div class="cotec-kpi-detail">Material que aún está en circulación.</div>
                    <a class="cotec-kpi-action" href="{{ route('prestamos.index', ['estado' => 'abiertos']) }}">Ver préstamos activos <span aria-hidden="true">→</span></a>
                </article>

                <article class="cotec-kpi-card">
                    <div class="cotec-kpi-icon blue"><i class="fa-solid fa-bookmark" aria-hidden="true"></i></div>
                    <div class="cotec-kpi-value">{{ $reservasActivas }}</div>
                    <div class="cotec-kpi-label">Reservas activas</div>
                    <div class="cotec-kpi-detail">Solicitudes pendientes de retiro o entrega.</div>
                    <a class="cotec-kpi-action" href="{{ route('reservas.todas', ['estado' => 'activa']) }}">Gestionar reservas <span aria-hidden="true">→</span></a>
                </article>

                <article class="cotec-kpi-card">
                    <div class="cotec-kpi-icon green"><i class="fa-solid fa-book" aria-hidden="true"></i></div>
                    <div class="cotec-kpi-value">{{ $totalLibros }}</div>
                    <div class="cotec-kpi-label">Títulos en catálogo</div>
                    <div class="cotec-kpi-detail">Referencias bibliográficas registradas.</div>
                    <a class="cotec-kpi-action" href="{{ route('libros.index') }}">Abrir catálogo administrativo <span aria-hidden="true">→</span></a>
                </article>

                <article class="cotec-kpi-card">
                    <div class="cotec-kpi-icon blue"><i class="fa-solid fa-chart-pie" aria-hidden="true"></i></div>
                    <div class="cotec-kpi-value">{{ $disponibilidadStock }}%</div>
                    <div class="cotec-kpi-label">Disponibilidad saludable</div>
                    <div class="cotec-kpi-detail">{{ $stockSaludable }} títulos superan el umbral operativo.</div>
                </article>

                <article class="cotec-kpi-card">
                    <div class="cotec-kpi-icon blue"><i class="fa-solid fa-users" aria-hidden="true"></i></div>
                    <div class="cotec-kpi-value">{{ $totalUsuarios }}</div>
                    <div class="cotec-kpi-label">Usuarios registrados</div>
                    <div class="cotec-kpi-detail">Cuentas registradas en la plataforma.</div>
                    <a class="cotec-kpi-action" href="{{ route('usuarios.index') }}">Administrar usuarios <span aria-hidden="true">→</span></a>
                </article>
            </section>

            <div class="cotec-dashboard-section-heading">
                <h2>Actividad y demanda</h2>
                <p>Préstamos y solicitudes de los últimos 30 días</p>
            </div>
            <section class="cotec-dashboard-panels" aria-label="Actividad y demanda de la biblioteca">
                <article class="cotec-dashboard-panel">
                    <h3>Libros más prestados</h3>
                    <p class="cotec-dashboard-panel-intro">Últimos 30 días · {{ $periodoDesde->format('d/m/Y') }} – {{ $periodoHasta->format('d/m/Y') }} · {{ $prestamosUltimos30Dias }} préstamos</p>
                    @forelse ($masPrestados as $l)
                        <div class="cotec-dashboard-bar-row">
                            <div class="cotec-dashboard-bar-label">
                                <span>{{ $l->titulo }}</span><strong>{{ $l->total }}</strong>
                            </div>
                            <div class="cotec-dashboard-bar-track" role="img" aria-label="{{ $l->total }} préstamos">
                                <div class="cotec-dashboard-bar" style="width:{{ round($l->total / max(1, (int) $masPrestados->max('total')) * 100) }}%;"></div>
                            </div>
                        </div>
                    @empty
                        <p class="cotec-dashboard-panel-intro">No hay préstamos registrados en este periodo.</p>
                    @endforelse
                </article>

                <article class="cotec-dashboard-panel">
                    <h3>Préstamos por mes</h3>
                    <p class="cotec-dashboard-panel-intro">Evolución de los últimos seis meses.</p>
                    @php $maxMes = max(1, max(array_column($prestamosPorMes, 'total'))); @endphp
                    @foreach ($prestamosPorMes as $m)
                        <div class="cotec-dashboard-bar-row">
                            <div class="cotec-dashboard-bar-label">
                                <span>{{ ucfirst($m['mes']) }}</span><strong>{{ $m['total'] }}</strong>
                            </div>
                            <div class="cotec-dashboard-bar-track" role="img" aria-label="{{ $m['total'] }} préstamos">
                                <div class="cotec-dashboard-bar blue" style="width:{{ round($m['total'] / $maxMes * 100) }}%;"></div>
                            </div>
                        </div>
                    @endforeach
                </article>

                <article class="cotec-dashboard-panel">
                    <h3>Demanda por carrera</h3>
                    <p class="cotec-dashboard-panel-intro">Préstamos de los últimos 30 días.</p>
                    @php $maxCarrera = max(1, (int) $demandaPorCarrera->max('total')); @endphp
                    @forelse ($demandaPorCarrera as $fila)
                        <div class="cotec-dashboard-bar-row">
                            <div class="cotec-dashboard-bar-label">
                                <span>{{ $fila->carrera }}</span><strong>{{ $fila->total }}</strong>
                            </div>
                            <div class="cotec-dashboard-bar-track" role="img" aria-label="{{ $fila->total }} préstamos">
                                <div class="cotec-dashboard-bar blue" style="width:{{ round($fila->total / $maxCarrera * 100) }}%;"></div>
                            </div>
                        </div>
                    @empty
                        <p class="cotec-dashboard-panel-intro">No hay préstamos por carrera en este periodo.</p>
                    @endforelse
                </article>

                <article class="cotec-dashboard-panel">
                    <h3>Títulos con lista de espera</h3>
                    <p class="cotec-dashboard-panel-intro">Personas en espera: <strong>{{ $personasEnEspera }}</strong></p>
                    @php $maxEspera = max(1, (int) $titulosEnEspera->max('total')); @endphp
                    @forelse ($titulosEnEspera as $titulo)
                        <div class="cotec-dashboard-bar-row">
                            <div class="cotec-dashboard-bar-label">
                                <span>{{ $titulo->titulo }}</span><strong>{{ $titulo->total }}</strong>
                            </div>
                            <div class="cotec-dashboard-bar-track" role="img" aria-label="{{ $titulo->total }} personas en espera">
                                <div class="cotec-dashboard-bar amber" style="width:{{ round($titulo->total / $maxEspera * 100) }}%;"></div>
                            </div>
                        </div>
                    @empty
                        <p class="cotec-dashboard-panel-intro">No hay personas en lista de espera.</p>
                    @endforelse
                </article>
            </section>

        </div>
    </div>
</x-app-layout>