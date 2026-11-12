<div class="ui-book-cover {{ $clase ?? '' }}">
    <span class="ui-book-cover-placeholder" aria-hidden="true">
        <i class="fa-solid fa-book-open"></i>
        <span>Sin portada disponible</span>
    </span>
    @if ($libro->portada_url)
        <img src="{{ $libro->portada_url }}" alt="" loading="lazy" decoding="async" onerror="this.hidden = true">
    @endif
</div>
