<x-app-layout>
    <x-slot name="header"><h2 class="ui-title">{{ $libro->titulo }}</h2></x-slot>
    @include('partials.ui')

    <style>
        .catalogo-detail {
            width: 100%;
            max-width: 820px;
            margin-inline: auto;
        }
    </style>

    @php
        $disponibles = $libro->ejemplares->where('estado', 'disponible');
        $porUbicacion = $disponibles->groupBy(fn ($e) => $e->ubicacion?->nombre ?? 'Sin ubicación registrada');
    @endphp

    <article class="cotec-panel ui-wrap catalogo-detail" aria-labelledby="libro-titulo">
        @include('partials.mensajes')

        @include('catalogo._portada', ['clase' => 'ui-book-cover--detail'])
        <h3 id="libro-titulo" class="ui-title">{{ $libro->titulo }}</h3>
        <p class="ui-muted">
            <span class="font-semibold">Autor(es):</span>
            {{ $libro->autores->pluck('nombre')->join(', ') ?: 'Autor no registrado' }}
            · {{ $libro->editorial->nombre }} · ISBN {{ $libro->isbn ?? '—' }}
        </p>
        <p style="margin:.75rem 0;">{{ $libro->descripcion ?: 'Sin descripción.' }}</p>
        @if ($libro->palabras_clave)
            <p class="ui-muted"><span class="font-semibold">Temas y palabras clave:</span> {{ $libro->palabras_clave }}</p>
        @endif

        @if ($libro->recurso_digital_url)
            <p>
                <a class="ui-btn ui-btn-light" href="{{ $libro->recurso_digital_url }}" target="_blank" rel="noopener noreferrer">
                    Consultar recurso digital <span class="sr-only">(se abre en una pestaña nueva)</span>
                </a>
            </p>
        @endif

        <div class="ui-inline" style="margin-bottom:1rem;" aria-label="Área temática y disponibilidad">
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
            @elseif ($miEspera?->estado === 'espera')
                <div class="ui-card">
                    <strong>Estás en la lista de espera</strong>
                    <span>Tu posición actual es {{ $posicionEspera }} de {{ $personasEnEspera }}.</span>
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
            @else
                <div class="ui-card">
                    <strong>Este título no tiene ejemplares disponibles.</strong>
                    @if ($miEspera?->estado === 'reservada')
                        <span>Ya se te asignó una reserva; revisa la fecha de retiro en tus reservas.</span>
                        <a href="{{ route('reservas.index') }}" class="ui-btn ui-btn-sm" style="align-self:flex-start;">Ver mis reservas</a>
                    @else
                        <span>{{ $personasEnEspera }} persona(s) en lista de espera.</span>
                        <form method="POST" action="{{ route('lista-espera.store', $libro) }}">
                            @csrf
                            <button class="ui-btn" type="submit">Unirme a la lista de espera</button>
                        </form>
                    @endif
                </div>
            @endif
        @else
            <div class="ui-card">
                Para reservar este libro o unirte a la lista de espera necesitas una cuenta.
                <div class="ui-inline">
                    <a class="ui-btn ui-btn-sm" href="{{ route('login') }}">Iniciar sesión</a>
                    <a class="ui-btn ui-btn-sm ui-btn-light" href="{{ route('register') }}">Registrarme</a>
                </div>
            </div>
        @endauth

        <div style="margin-top:1rem;"><a href="{{ route('catalogo.index') }}" class="ui-btn ui-btn-light ui-btn-sm">← Volver al catálogo</a></div>
    </article>
</x-app-layout>