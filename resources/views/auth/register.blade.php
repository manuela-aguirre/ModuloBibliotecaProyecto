<x-guest-layout>

    <style>
        .cotec-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            background: linear-gradient(135deg, #15803d 0%, #16a34a 50%, #34d399 100%);
            font-family: 'Figtree', Arial, sans-serif;
        }

        .cotec-card {
            width: 100%;
            max-width: 440px;
            background: #ffffff;
            border-radius: 1rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.25);
            overflow: hidden;
            border: 1px solid #dcfce7;
        }

        .cotec-header {
            background: linear-gradient(90deg, #14532d 0%, #16a34a 100%);
            padding: 2.5rem 2rem;
            text-align: center;
            color: #ffffff;
        }

        .cotec-logo-circle {
            width: 92px;
            height: 92px;
            background: #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem auto;
            box-shadow: 0 6px 18px rgba(0,0,0,0.2);
            box-sizing: border-box;
            border: 4px solid rgba(255,255,255,0.5);
            overflow: hidden;
        }

        .cotec-logo-circle svg {
            width: 100%;
            height: 100%;
            display: block;
        }

        .cotec-header h1 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 0.03em;
        }

        .cotec-header .subtitle {
            margin-top: 0.25rem;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: #dcfce7;
            font-weight: 600;
        }

        .cotec-header .slogan {
            margin-top: 0.75rem;
            font-size: 0.85rem;
            font-style: italic;
            color: #ecfdf5;
        }

        .cotec-body {
            padding: 2rem;
        }

        .cotec-body h2 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
            text-align: center;
        }

        .cotec-body .welcome {
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: #6b7280;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .cotec-benefits {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 0.6rem;
            padding: 0.85rem 1rem;
            margin-bottom: 1.5rem;
        }

        .cotec-benefits p {
            margin: 0 0 0.4rem 0;
            font-size: 0.8rem;
            font-weight: 600;
            color: #15803d;
        }

        .cotec-benefits ul {
            margin: 0;
            padding-left: 1.1rem;
        }

        .cotec-benefits li {
            font-size: 0.78rem;
            color: #166534;
            margin-bottom: 0.15rem;
        }

        .cotec-field {
            margin-bottom: 1.25rem;
        }

        .cotec-field label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .cotec-input-wrap {
            position: relative;
        }

        .cotec-input-wrap svg {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
        }

        .cotec-input-wrap input,
        .cotec-input-wrap select {
            width: 100%;
            box-sizing: border-box;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.95rem;
            font-family: inherit;
            transition: border-color 0.2s, box-shadow 0.2s;
            background: #fff;
        }

        .cotec-input-wrap input:focus,
        .cotec-input-wrap select:focus {
            outline: none;
            border-color: #16a34a;
            box-shadow: 0 0 0 3px rgba(22,163,74,0.2);
        }

        .cotec-submit {
            width: 100%;
            background: #15803d;
            color: #ffffff;
            font-weight: 600;
            font-size: 0.95rem;
            padding: 0.85rem 1rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: background 0.2s, box-shadow 0.2s;
            margin-top: 0.5rem;
        }

        .cotec-submit:hover {
            background: #14532d;
            box-shadow: 0 6px 10px rgba(0,0,0,0.15);
        }

        .cotec-login-link {
            text-align: center;
            font-size: 0.875rem;
            color: #4b5563;
            margin-top: 1.5rem;
        }

        .cotec-login-link a {
            color: #15803d;
            font-weight: 600;
            text-decoration: none;
        }

        .cotec-login-link a:hover {
            text-decoration: underline;
            color: #14532d;
        }

        .cotec-footer {
            margin-top: 2rem;
            padding-top: 1.25rem;
            border-top: 1px solid #f3f4f6;
            text-align: center;
        }

        .cotec-footer p {
            margin: 0.2rem 0;
            font-size: 0.75rem;
            color: #9ca3af;
        }

        .cotec-copyright {
            text-align: center;
            color: rgba(255,255,255,0.85);
            font-size: 0.75rem;
            margin-top: 1.5rem;
        }

        .cotec-error {
            color: #dc2626;
            font-size: 0.8rem;
            margin-top: 0.4rem;
        }

        @media (max-width: 480px) {
            .cotec-header { padding: 2rem 1.25rem; }
            .cotec-body { padding: 1.5rem; }
        }
    </style>

    <div class="cotec-wrapper">
        <div style="width:100%; max-width:440px;">

            <div class="cotec-card">

                <!-- Encabezado -->
                <div class="cotec-header">
                    <div class="cotec-logo-circle" style="background: transparent; border-radius: 0; border: none; box-shadow: none; width: 88px; height: 88px;">
                        <img
                            src="{{ file_exists(public_path('build/images/logo-cotecnova.png')) ? asset('build/images/logo-cotecnova.png') : asset('build/images/logo-cotecnova.svg') }}"
                            alt="Logo COTECNOVA"
                            style="width:100%; height:100%; object-fit:contain; display:block;"
                        >
                    </div>

                    <h1>Biblioteca COTECNOVA</h1>
                    <p class="subtitle">ERP Universitario</p>
                    <p class="slogan">"Conocimiento al alcance de un clic"</p>
                </div>

                <!-- Formulario -->
                <div class="cotec-body">

                    <h2>Crear cuenta - COTECNOVA ERP</h2>
                    <p class="welcome">Regístrate para acceder al sistema de la biblioteca.</p>

                    <div class="cotec-benefits">
                        <p>Al registrarte podrás:</p>
                        <ul>
                            <li>Consultar y reservar libros disponibles</li>
                            <li>Ver tu historial de préstamos</li>
                            <li>Recibir notificaciones de vencimiento</li>
                        </ul>
                    </div>

                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <!-- Nombre -->
                        <div class="cotec-field">
                            <label for="name">Nombre completo</label>
                            <div class="cotec-input-wrap">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" style="width:1.1rem;height:1.1rem;">
                                    <circle cx="12" cy="8" r="4"/>
                                    <path d="M4 21v-1a6 6 0 0 1 6-6h4a6 6 0 0 1 6 6v1"/>
                                </svg>
                                <input
                                    id="name"
                                    type="text"
                                    name="name"
                                    value="{{ old('name') }}"
                                    required
                                    autofocus
                                    autocomplete="name"
                                    placeholder="Juan Pérez"
                                />
                            </div>
                            @error('name')
                                <p class="cotec-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tipo y número de identificación -->
                        <div class="cotec-field">
                            <label for="tipo_identificacion">Tipo de identificación</label>
                            <div class="cotec-input-wrap">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" style="width:1.1rem;height:1.1rem;">
                                    <rect x="2" y="5" width="20" height="14" rx="2"/>
                                    <line x1="2" y1="10" x2="22" y2="10"/>
                                </svg>
                                <select id="tipo_identificacion" name="tipo_identificacion" required>
                                    <option value="" disabled {{ old('tipo_identificacion') ? '' : 'selected' }}>Selecciona una opción</option>
                                    <option value="CC" {{ old('tipo_identificacion') == 'CC' ? 'selected' : '' }}>Cédula de ciudadanía (CC)</option>
                                    <option value="TI" {{ old('tipo_identificacion') == 'TI' ? 'selected' : '' }}>Tarjeta de identidad (TI)</option>
                                </select>
                            </div>
                            @error('tipo_identificacion')
                                <p class="cotec-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="cotec-field">
                            <label for="numero_identificacion">Número de identificación</label>
                            <div class="cotec-input-wrap">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" style="width:1.1rem;height:1.1rem;">
                                    <rect x="2" y="5" width="20" height="14" rx="2"/>
                                    <line x1="2" y1="10" x2="22" y2="10"/>
                                </svg>
                                <input
                                    id="numero_identificacion"
                                    type="text"
                                    name="numero_identificacion"
                                    value="{{ old('numero_identificacion') }}"
                                    required
                                    inputmode="numeric"
                                    placeholder="Ej: 1094xxxxxx"
                                />
                            </div>
                            @error('numero_identificacion')
                                <p class="cotec-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="cotec-field">
                            <label for="telefono">Teléfono (opcional)</label>
                            <div class="cotec-input-wrap">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" style="width:1.1rem;height:1.1rem;">
                                    <rect x="2" y="5" width="20" height="14" rx="2"/>
                                    <line x1="2" y1="10" x2="22" y2="10"/>
                                </svg>
                                <input
                                    id="telefono"
                                    type="tel" inputmode="tel"
                                    name="telefono"
                                    value="{{ old('telefono') }}"
                                    
                                    placeholder="Ej: 3001234567"
                                />
                            </div>
                            @error('telefono')
                                <p class="cotec-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="cotec-field">
                            <label for="carrera">Carrera o programa</label>
                            <div class="cotec-input-wrap">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" style="width:1.1rem;height:1.1rem;">
                                    <rect x="2" y="5" width="20" height="14" rx="2"/>
                                    <line x1="2" y1="10" x2="22" y2="10"/>
                                </svg>
                                <input
                                    id="carrera"
                                    type="text"
                                    name="carrera"
                                    value="{{ old('carrera') }}"
                                    required
                                    placeholder="Ej: Tecnología en Sistemas de Información"
                                />
                            </div>
                            @error('carrera')
                                <p class="cotec-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="cotec-field">
                            <label for="semestre">Semestre</label>
                            <div class="cotec-input-wrap">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" style="width:1.1rem;height:1.1rem;">
                                    <rect x="2" y="5" width="20" height="14" rx="2"/>
                                    <line x1="2" y1="10" x2="22" y2="10"/>
                                </svg>
                                <input
                                    id="semestre"
                                    type="number" min="1" max="12"
                                    name="semestre"
                                    value="{{ old('semestre') }}"
                                    required
                                    placeholder="Ej: 4"
                                />
                            </div>
                            @error('semestre')
                                <p class="cotec-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Correo -->
                        <div class="cotec-field">
                            <label for="email">Correo electrónico</label>
                            <div class="cotec-input-wrap">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" style="width:1.1rem;height:1.1rem;">
                                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                                    <path d="m22 7-10 5L2 7"/>
                                </svg>
                                <input
                                    id="email"
                                    type="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    required
                                    autocomplete="username"
                                    placeholder="correo@ejemplo.com"
                                />
                            </div>
                            @error('email')
                                <p class="cotec-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Contraseña -->
                        <div class="cotec-field">
                            <label for="password">Contraseña</label>
                            <div class="cotec-input-wrap">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" style="width:1.1rem;height:1.1rem;">
                                    <rect x="3" y="11" width="18" height="11" rx="2"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                                <input
                                    id="password"
                                    type="password"
                                    name="password"
                                    required
                                    autocomplete="new-password"
                                    placeholder="••••••••"
                                />
                            </div>
                            @error('password')
                                <p class="cotec-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Confirmar contraseña -->
                        <div class="cotec-field">
                            <label for="password_confirmation">Confirmar contraseña</label>
                            <div class="cotec-input-wrap">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" style="width:1.1rem;height:1.1rem;">
                                    <rect x="3" y="11" width="18" height="11" rx="2"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                                <input
                                    id="password_confirmation"
                                    type="password"
                                    name="password_confirmation"
                                    required
                                    autocomplete="new-password"
                                    placeholder="••••••••"
                                />
                            </div>
                            @error('password_confirmation')
                                <p class="cotec-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Autorización de tratamiento de datos (Ley 1581 de 2012) -->
                        <div class="cotec-field">
                            <label style="display:flex;gap:.55rem;align-items:flex-start;font-weight:400;">
                                <input type="checkbox" name="acepta_datos" value="1" required {{ old('acepta_datos') ? 'checked' : '' }} style="margin-top:.2rem;width:auto;">
                                <span>
                                    Autorizo el tratamiento de mis datos personales conforme a la Ley 1581 de 2012.
                                    <a href="{{ route('politica.datos') }}" target="_blank" style="color:#14532d;font-weight:600;">Ver política</a>
                                </span>
                            </label>
                            @error('acepta_datos')
                                <p class="cotec-error">Debes autorizar el tratamiento de tus datos para registrarte.</p>
                            @enderror
                        </div>

                        <!-- Botón -->
                        <button type="submit" class="cotec-submit">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" style="width:1.1rem;height:1.1rem;">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <line x1="19" y1="8" x2="19" y2="14"/>
                                <line x1="22" y1="11" x2="16" y2="11"/>
                            </svg>
                            Registrarse
                        </button>

                    </form>

                    <!-- Enlace a login -->
                    <p class="cotec-login-link">
                        ¿Ya tienes una cuenta?
                        <a href="{{ route('login') }}">Inicia sesión aquí</a>
                    </p>

                    <!-- Pie -->
                    <div class="cotec-footer">
                        <p>Biblioteca Universidad COTECNOVA</p>
                    </div>

                </div>
            </div>

            <p class="cotec-copyright">&copy; {{ date('Y') }} COTECNOVA. Todos los derechos reservados.</p>

        </div>
    </div>
</x-guest-layout>