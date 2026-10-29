@if (session('status'))
    <div style="background:#dcfce7;color:#14532d;padding:.75rem 1rem;border-radius:.75rem;margin-bottom:1rem;font-weight:600;font-size:.88rem;">
        {{ session('status') }}
    </div>
@endif

@if ($errors->any())
    <div style="background:#fee2e2;color:#991b1b;padding:.75rem 1rem;border-radius:.75rem;margin-bottom:1rem;font-weight:600;font-size:.88rem;">
        {{ $errors->first() }}
    </div>
@endif