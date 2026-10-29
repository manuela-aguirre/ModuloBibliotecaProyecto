<x-app-layout>
    <x-slot name="header"><h2 class="ui-title">{{ $libro->titulo }}</h2></x-slot>
    @include('partials.ui')

    @php
        $disponibles = $libro->ejemplares->where('estado', 'disponible');
        $porUbicacion = $disponibles->groupBy(fn ($e) => $e->ubicacion?->nombre ?? 'Sin ubicación registrada');
    @endphp

    <div class="cotec-panel ui-wrap" style="max-width:820px;">
        @include('partials.mensajes')

        <p class="ui-muted">
            {{ $libro->autores->pluck('nombre')->join(', ') ?: 'Autor no registrado' }}
            · {{ $libro->editorial->nombre }} · ISBN {{ $libro->isbn ?? '—' }}
        </p>
        <p style="margin:.75rem 0;">{{ $libro->descripcion ?: 'Sin descripción.' }}</p>
        @if ($libro->palabras_clave)
            <p class="ui-muted">Palabras clave: {{ $libro->palabras_clave }}</p>
        @endif

        <div class="ui-inline" style="margin-bottom:1rem;">
            <span class="ui-badge">{{ $libro->genero->nombre }}</span>
            @if ($disponibles->count() > 0)
                <span class="ui-badge ok">{{ $disponibles->count() }} de {{ $libro->ejemplares->count() }} disponible(s)</span>
            @else
                <span class="ui-badge bad">Sin ejemplares disponibles</span>
            @endif
        </div>

        @if ($porUbicacion->isNotEmpty())
            <div class="ui-card" style="margin-bottom:1rem;">
                <strong style="color:#14532d;">¿Dónde está?</strong>
                @foreach ($porUbicacion as $ubicacion => $copias)
                    <div>{{ $ubicacion }} <span class="ui-muted">({{ $copias->count() }})</span></div>
                @endforeach
            </div>
        @endif

        @auth
            @if ($miReserva)
                <div class="ui-card">
                    Ya reservaste este libro. Lo esperamos el <strong>{{ $miReserva->fecha_retiro->format('d/m/Y') }}</strong>.
                    <a href="{{ route('reservas.index') }}" class="ui-btn ui-btn-sm" style="align-self:flex-start;">Ver mis reservas</a>
                </div>
            @elseif ($disponibles->count() > 0)
                <form method="POST" action="{{ route('reservas.store', $libro) }}" class="ui-card">
                    @csrf
                    <div class="ui-muted">
                        Reservarás a nombre de <strong>{{ auth()->user()->name }}</strong>
                        ({{ auth()->user()->tipo_identificacion }} {{ auth()->user()->numero_identificacion }})
                        @if (auth()->user()->carrera) · {{ auth()->user()->carrera }} @endif
                        @if (auth()->user()->semestre) · Semestre {{ auth()->user()->semestre }} @endif
                        @unless (auth()->user()->carrera && auth()->user()->semestre)
                            <br><a href="{{ route('profile.edit') }}" style="color:#14532d;font-weight:600;">Completa tu carrera y semestre en tu perfil</a>
                        @endunless
                    </div>
                    <label class="ui-label" for="fecha_retiro">¿Qué día pasarás a reclamarlo?</label>
                    <input class="ui-input" style="max-width:240px" type="date" id="fecha_retiro" name="fecha_retiro"
                           min="{{ today()->toDateString() }}"
                           max="{{ today()->addDays(config('biblioteca.dias_max_reserva'))->toDateString() }}"
                           value="{{ old('fecha_retiro', today()->toDateString()) }}" required>
                    <span class="ui-muted">Puedes elegir hasta {{ config('biblioteca.dias_max_reserva') }} días desde hoy.</span>
                    <button class="ui-btn" style="align-self:flex-start;">Reservar libro</button>
                </form>
            @endif
        @else
            <div class="ui-card">
                Para reservar este libro necesitas una cuenta.
                <div class="ui-inline">
                    <a class="ui-btn ui-btn-sm" href="{{ route('login') }}">Iniciar sesión</a>
                    <a class="ui-btn ui-btn-sm ui-btn-light" href="{{ route('register') }}">Registrarme</a>
                </div>
            </div>
        @endauth

        <div style="margin-top:1rem;"><a href="{{ route('catalogo.index') }}" class="ui-btn ui-btn-light ui-btn-sm">← Volver al catálogo</a></div>
    </div>
</x-app-layout>