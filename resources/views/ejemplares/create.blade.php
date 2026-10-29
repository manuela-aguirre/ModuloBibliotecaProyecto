<x-app-layout>
    <x-slot name="header"><h2 class="ui-title">Nuevo ejemplar: {{ $libro->titulo }}</h2></x-slot>
    @include('partials.ui')

    <div class="cotec-panel ui-wrap">
        @include('partials.mensajes')
        <form method="POST" action="{{ route('libros.ejemplares.store', $libro) }}">
            @csrf
            @include('ejemplares._form')
        </form>
    </div>
</x-app-layout>