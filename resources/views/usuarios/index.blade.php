<x-app-layout>
    <x-slot name="header">
        <h2 class="cotec-header-title">
            Usuarios registrados
        </h2>
    </x-slot>

    <style>
        .cotec-header-title {
            font-family: 'Figtree', Arial, sans-serif;
            font-weight: 600;
            font-size: 1.25rem;
            color: #14532d;
        }

        .usuarios-wrap {
            padding: 2rem 1rem;
            font-family: 'Figtree', Arial, sans-serif;
        }

        .usuarios-panel {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border: 1px solid #dfeee3;
            border-radius: 1.2rem;
            box-shadow: 0 14px 30px rgba(20, 83, 45, 0.08);
            overflow: hidden;
        }

        .usuarios-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            background: linear-gradient(90deg, #f0fdf4 0%, #ecfdf5 100%);
            border-bottom: 1px solid #d1fae5;
            padding: 1.3rem 1.5rem;
        }

        .usuarios-title {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            color: #14532d;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .usuarios-icon {
            width: 42px;
            height: 42px;
            border-radius: 0.8rem;
            background: #14532d;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .usuarios-summary {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
            padding: 1.5rem;
            border-bottom: 1px solid #eef2f7;
            background: #fafcfb;
        }

        @media (max-width: 768px) {
            .usuarios-summary {
                grid-template-columns: 1fr;
            }
        }

        .usuarios-stat {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: 1rem 1.1rem;
        }

        .usuarios-stat-label {
            color: #64748b;
            font-size: 0.76rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            font-weight: 700;
        }

        .usuarios-stat-value {
            margin-top: 0.5rem;
            font-size: 1.8rem;
            font-weight: 700;
            color: #111827;
        }

        .usuarios-table-wrap {
            overflow-x: auto;
            padding: 1rem 1.5rem 1.5rem;
        }

        table.usuarios-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 760px;
        }

        .usuarios-table th {
            background: #f0fdf4;
            color: #14532d;
            text-transform: uppercase;
            font-size: 0.72rem;
            letter-spacing: 0.08em;
            padding: 0.9rem 1rem;
            text-align: left;
        }

        .usuarios-table td {
            padding: 0.9rem 1rem;
            border-top: 1px solid #edf2f7;
            color: #334155;
            font-size: 0.92rem;
        }

        .usuarios-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.38rem 0.7rem;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 700;
        }

        .usuarios-badge.verified {
            background: #dcfce7;
            color: #166534;
        }

        .usuarios-badge.pending {
            background: #fef3c7;
            color: #92400e;
        }

        .empty-state {
            padding: 2rem 1.5rem 2.5rem;
            text-align: center;
            color: #64748b;
        }
    </style>

    <div class="usuarios-wrap">
        <div class="usuarios-panel">
            <div class="usuarios-topbar">
                <div class="usuarios-title">
                    <div class="usuarios-icon">
                        <i class="fa-solid fa-user-group"></i>
                    </div>
                    Usuarios registrados en la plataforma
                </div>
            </div>

            <div class="usuarios-summary">
                <div class="usuarios-stat">
                    <div class="usuarios-stat-label">Total</div>
                    <div class="usuarios-stat-value">{{ $totalUsuarios ?? 0 }}</div>
                </div>
                <div class="usuarios-stat">
                    <div class="usuarios-stat-label">Verificados</div>
                    <div class="usuarios-stat-value">{{ $usuariosVerificados ?? 0 }}</div>
                </div>
                <div class="usuarios-stat">
                    <div class="usuarios-stat-label">Pendientes</div>
                    <div class="usuarios-stat-value">{{ ($totalUsuarios ?? 0) - ($usuariosVerificados ?? 0) }}</div>
                </div>
            </div>

            @if ($usuarios->isEmpty())
                <div class="empty-state">No hay usuarios registrados todavía.</div>
            @else
                <div class="usuarios-table-wrap">
                    @include('partials.ui')
                    @include('partials.mensajes')
                    @error('rol') <p style="color:#991b1b;font-size:.85rem;padding:.5rem 1.5rem;">{{ $message }}</p> @enderror
                    <table class="usuarios-table">
                        <thead>
                            <tr>
                                <th>Identificación</th>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Email</th>
                                <th>Estado</th>
                                <th>Registro</th>
                                <th>Rol y límite</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($usuarios as $usuario)
                                <tr>
                                    <td>#{{ $usuario->numero_identificacion ?? 'N/A' }}</td>
                                    <td>{{ $usuario->name }}</td>
                                    <td>{{ $usuario->tipo_identificacion ?? 'No definido' }}</td>
                                    <td>{{ $usuario->email }}</td>
                                    <td>
                                        @if ($usuario->email_verified_at)
                                            <span class="usuarios-badge verified">Verificado</span>
                                        @else
                                            <span class="usuarios-badge pending">Pendiente</span>
                                        @endif
                                    </td>
                                    <td>{{ $usuario->created_at ? $usuario->created_at->format('d/m/Y H:i') : 'Sin fecha' }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('usuarios.update', $usuario) }}" style="display:flex;gap:.4rem;align-items:center;flex-wrap:wrap;">
                                            @csrf @method('PATCH')
                                            <select name="rol" style="border-radius:.5rem;border:1px solid #dcfce7;padding:.25rem .5rem;font-size:.8rem;">
                                                <option value="usuario" @selected($usuario->rol === 'usuario')>Usuario</option>
                                                <option value="administrador" @selected($usuario->rol === 'administrador')>Bibliotecario</option>
                                            </select>
                                            <input type="number" name="limite_prestamos" min="1" max="10" value="{{ $usuario->limite_prestamos ?? 3 }}" title="Límite de préstamos" style="width:4.2rem;border-radius:.5rem;border:1px solid #dcfce7;padding:.25rem .4rem;font-size:.8rem;">
                                            <button style="background:#14532d;color:#fff;border:0;border-radius:.6rem;padding:.3rem .7rem;font-size:.75rem;font-weight:600;cursor:pointer;">Guardar</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
