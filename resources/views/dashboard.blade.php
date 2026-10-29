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
            padding: 2rem 1rem;
            font-family: 'Figtree', Arial, sans-serif;
        }

        .cotec-dash-inner {
            max-width: 1100px;
            margin: 0 auto;
        }

        .cotec-welcome-card {
            background: linear-gradient(90deg, #14532d 0%, #16a34a 100%);
            border-radius: 1rem;
            padding: 2rem;
            color: #ffffff;
            box-shadow: 0 10px 25px rgba(0,0,0,0.12);
            margin-bottom: 2rem;
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
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 1.25rem;
        }

        @media (max-width: 1150px) {
            .cotec-kpi-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }

        @media (max-width: 900px) {
            .cotec-kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 520px) {
            .cotec-kpi-grid { grid-template-columns: 1fr; }
        }

        .cotec-kpi-card {
            position: relative;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 0.85rem;
            padding: 1.25rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
            transition: box-shadow 0.2s, transform 0.2s;
            overflow: visible;
        }

        .cotec-kpi-card:hover {
            box-shadow: 0 8px 18px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

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
            border-radius: 0.65rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cotec-kpi-icon.green { background: #dcfce7; }
        .cotec-kpi-icon.blue { background: #dbeafe; }
        .cotec-kpi-icon.amber { background: #fef3c7; }
        .cotec-kpi-icon.red { background: #fee2e2; }

        .cotec-kpi-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: #111827;
        }

        .cotec-kpi-label {
            font-size: 0.85rem;
            color: #6b7280;
        }

        .cotec-kpi-card.alert .cotec-kpi-value {
            color: #b91c1c;
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
            <div class="cotec-kpi-grid">

                <div class="cotec-kpi-card" tabindex="0">
                    <div class="cotec-kpi-icon green">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2" style="width:22px;height:22px;">
                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                        </svg>
                    </div>
                    <div class="cotec-kpi-value">{{ $totalLibros ?? 0 }}</div>
                    <div class="cotec-kpi-label">Títulos en catálogo</div>
                    <div class="cotec-kpi-tooltip">Total de referencias disponibles en el sistema para consulta y gestión.</div>
                </div>

                <div class="cotec-kpi-card" tabindex="0">
                    <div class="cotec-kpi-icon blue">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" style="width:22px;height:22px;">
                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                            <path d="M9 8h7M9 12h7M9 16h7"/>
                        </svg>
                    </div>
                    <div class="cotec-kpi-value">{{ $disponibilidadStock ?? 0 }}%</div>
                    <div class="cotec-kpi-label">Disponibilidad del stock</div>
                    <div class="cotec-kpi-tooltip">Porcentaje de títulos con existencia saludable y sin riesgo operativo por escasez.</div>
                </div>

                <div class="cotec-kpi-card" tabindex="0">
                    <div class="cotec-kpi-icon amber">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" style="width:22px;height:22px;">
                            <path d="M12 2v20"/>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14.5a3.5 3.5 0 0 1 0 7H6"/>
                        </svg>
                    </div>
                    <div class="cotec-kpi-value">{{ $prestamosActivos ?? 0 }}</div>
                    <div class="cotec-kpi-label">Préstamos activos</div>
                    <div class="cotec-kpi-tooltip">Material que está actualmente en circulación y aún no ha sido devuelto.</div>
                </div>

                <div class="cotec-kpi-card alert" tabindex="0">
                    <div class="cotec-kpi-icon red">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" style="width:22px;height:22px;">
                            <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                            <line x1="12" y1="9" x2="12" y2="13"/>
                            <line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                    </div>
                    <div class="cotec-kpi-value">{{ $librosBajoStock ?? 0 }}</div>
                    <div class="cotec-kpi-label">Stock crítico</div>
                    <div class="cotec-kpi-tooltip">Títulos con escasez de ejemplares o disponibilidad muy baja para atender la demanda.</div>
                </div>

                <div class="cotec-kpi-card alert" tabindex="0">
                    <div class="cotec-kpi-icon red">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#b91c1c" stroke-width="2" style="width:22px;height:22px;">
                            <path d="M12 8v4"/>
                            <path d="M12 16h.01"/>
                            <path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                        </svg>
                    </div>
                    <div class="cotec-kpi-value">{{ $tasaVencimiento ?? 0 }}%</div>
                    <div class="cotec-kpi-label">Tasa de vencimiento</div>
                    <div class="cotec-kpi-tooltip">Porcentaje de préstamos activos que están fuera de plazo, indicador clave de morosidad.</div>
                </div>

                <div class="cotec-kpi-card" tabindex="0">
                    <div class="cotec-kpi-icon amber">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" style="width:22px;height:22px;">
                            <path d="M12 1v22"/>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14.5a3.5 3.5 0 0 1 0 7H6"/>
                        </svg>
                    </div>
                    <div class="cotec-kpi-value">${{ number_format($multasPendientes ?? 0, 0, ',', '.') }}</div>
                    <div class="cotec-kpi-label">Multas pendientes</div>
                    <div class="cotec-kpi-tooltip">Suma de las multas generadas por devoluciones tardías que aún no se han pagado.</div>
                </div>

                <div class="cotec-kpi-card" tabindex="0">
                    <div class="cotec-kpi-icon green">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2" style="width:22px;height:22px;">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                            <path d="M8 9h8M8 13h5"/>
                        </svg>
                    </div>
                    <div class="cotec-kpi-value">{{ $usoCatalogo ?? 0 }}%</div>
                    <div class="cotec-kpi-label">Uso del catálogo</div>
                    <div class="cotec-kpi-tooltip">Relación entre préstamos activos y títulos del catálogo para medir demanda real.</div>
                </div>

                <div class="cotec-kpi-card" tabindex="0">
                    <div class="cotec-kpi-icon blue">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" style="width:22px;height:22px;">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </div>
                    <div class="cotec-kpi-value">{{ $totalUsuarios ?? 0 }}</div>
                    <div class="cotec-kpi-label">Usuarios registrados</div>
                    <div class="cotec-kpi-tooltip">Cantidad real de usuarios registrados en la plataforma, independientemente del uso del catálogo.</div>
                </div>

            </div>

            <div style="margin-top:2rem;display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));">
                <div style="border:1px solid #dcfce7;border-radius:1rem;background:#fff;padding:1.1rem;">
                    <div style="font-size:.72rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#14532d;">Libros más prestados</div>
                    @forelse ($masPrestados as $l)
                        <div style="margin-top:.8rem;">
                            <div style="display:flex;justify-content:space-between;gap:1rem;font-size:.88rem;color:#334155;">
                                <span>{{ $l->titulo }}</span><strong>{{ $l->total }}</strong>
                            </div>
                            <div style="margin-top:.3rem;height:8px;border-radius:999px;background:#f0fdf4;">
                                <div style="height:8px;border-radius:999px;background:#16a34a;width:{{ round($l->total / $masPrestados->first()->total * 100) }}%;"></div>
                            </div>
                        </div>
                    @empty
                        <p style="margin-top:.8rem;font-size:.88rem;color:#64748b;">Aún no hay préstamos registrados.</p>
                    @endforelse
                </div>

                <div style="border:1px solid #dcfce7;border-radius:1rem;background:#fff;padding:1.1rem;">
                    <div style="font-size:.72rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#14532d;">Préstamos por mes (últimos 6)</div>
                    @php $maxMes = max(1, max(array_column($prestamosPorMes, 'total'))); @endphp
                    @foreach ($prestamosPorMes as $m)
                        <div style="margin-top:.8rem;">
                            <div style="display:flex;justify-content:space-between;font-size:.88rem;color:#334155;">
                                <span>{{ ucfirst($m['mes']) }}</span><strong>{{ $m['total'] }}</strong>
                            </div>
                            <div style="margin-top:.3rem;height:8px;border-radius:999px;background:#eff6ff;">
                                <div style="height:8px;border-radius:999px;background:#2563eb;width:{{ round($m['total'] / $maxMes * 100) }}%;"></div>
                            </div>
                        </div>
                    @endforeach
                    <p style="margin-top:1rem;font-size:.82rem;color:#64748b;">Reservas activas ahora: <strong>{{ $reservasActivas }}</strong></p>
                </div>
            </div>

            <div class="mt-8 grid gap-4 md:grid-cols-1">
                <div class="rounded-2xl border border-[#dbeafe] bg-[#eff6ff] p-4">
                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-[#1d4ed8]">Cobertura</div>
                    <div class="mt-2 text-2xl font-bold text-[#1d4ed8]">{{ $stockSaludable ?? 0 }}</div>
                    <p class="mt-1 text-sm text-slate-600">Títulos con stock saludable por encima del umbral operativo.</p>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>