<x-app-layout>
    <x-slot name="header">
        <h2 class="ui-title">Mi perfil</h2>
    </x-slot>
    @include('partials.ui')

    <style>
        .profile-page { display: grid; gap: 1.25rem; }
        .profile-section { padding: 1.25rem; border: 1px solid #e2e8f0; border-radius: 1rem; background: #fff; }
        .profile-section h2 { color: #14532d; font-weight: 700; }
        .profile-section p { color: #64748b; }
        .profile-section label { color: #14532d; font-weight: 600; }
        .profile-section input:not([type=checkbox]) { border-color: #cbd5e1; border-radius: 0.75rem; }
        .profile-section input:focus { border-color: #15803d; outline: 3px solid rgba(21, 128, 61, 0.15); }
        .profile-section button { border-radius: 0.75rem; }
        @media (max-width: 640px) { .profile-section { padding: 1rem; } }
    </style>

    <div class="cotec-panel ui-wrap">
        <p class="ui-page-intro mb-5">Actualiza tu información personal y las opciones de seguridad de tu cuenta.</p>
        <div class="profile-page">
            <section class="profile-section">
                @include('profile.partials.update-profile-information-form')
            </section>
            <section class="profile-section">
                @include('profile.partials.update-password-form')
            </section>
            <section class="profile-section">
                @include('profile.partials.delete-user-form')
            </section>
        </div>
    </div>
</x-app-layout>
