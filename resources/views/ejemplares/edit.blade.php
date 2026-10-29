<x-app-layout>
    <x-slot name="header"><h2 class="ui-title">Editar ejemplar {{ $ejemplar->codigo_barras }}</h2></x-slot>
    @include('partials.ui')

    <div class="cotec-panel ui-wrap">
        @include('partials.mensajes')
        <form method="POST" action="{{ route('ejemplares.update', $ejemplar) }}">
            @csrf @method('PUT')
            @include('ejemplares._form')
        </form>
    </div>
</x-app-layout>