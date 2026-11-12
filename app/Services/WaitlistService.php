<?php

namespace App\Services;

use App\Models\Ejemplar;
use App\Models\ListaEspera;
use App\Models\Libro;
use App\Models\Reserva;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WaitlistService
{
    public function join(User $user, Libro $libro): ListaEspera
    {
        return DB::transaction(function () use ($user, $libro): ListaEspera {
            $libro = Libro::query()->lockForUpdate()->findOrFail($libro->id);

            if ($libro->ejemplares()->where('estado', 'disponible')->exists()) {
                throw ValidationException::withMessages([
                    'lista_espera' => 'El libro tiene ejemplares disponibles; puedes reservarlo desde el catálogo.',
                ]);
            }

            if ($user->tieneVencidos() || $user->cupoRestante() <= 0) {
                throw ValidationException::withMessages([
                    'lista_espera' => 'Para entrar a la lista no debes tener préstamos vencidos y debes tener cupo disponible.',
                ]);
            }

            if (Reserva::activas()->where('user_id', $user->id)
                ->whereHas('ejemplar', fn ($query) => $query->where('libro_id', $libro->id))
                ->exists()) {
                throw ValidationException::withMessages([
                    'lista_espera' => 'Ya tienes una reserva activa de este libro.',
                ]);
            }

            $entrada = ListaEspera::query()
                ->where('user_id', $user->id)
                ->where('libro_id', $libro->id)
                ->whereIn('estado', ['espera', 'reservada'])
                ->first();

            if ($entrada) {
                throw ValidationException::withMessages([
                    'lista_espera' => 'Ya estás en la lista de espera de este libro.',
                ]);
            }

            return ListaEspera::create([
                'user_id' => $user->id,
                'libro_id' => $libro->id,
                'estado' => 'espera',
            ]);
        });
    }

    public function assignNext(Libro $libro, InventoryService $inventory): bool
    {
        return DB::transaction(function () use ($libro, $inventory): bool {
            $libro = Libro::query()->lockForUpdate()->findOrFail($libro->id);
            $ejemplar = Ejemplar::query()
                ->where('libro_id', $libro->id)
                ->where('estado', 'disponible')
                ->lockForUpdate()
                ->first();

            if (! $ejemplar) {
                return false;
            }

            $entradas = ListaEspera::query()
                ->where('libro_id', $libro->id)
                ->where('estado', 'espera')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($entradas as $entrada) {
                $usuario = User::query()->lockForUpdate()->find($entrada->user_id);
                if (! $usuario) {
                    $entrada->update(['estado' => 'cancelada']);

                    continue;
                }

                $yaReservado = Reserva::activas()
                    ->where('user_id', $usuario->id)
                    ->whereHas('ejemplar', fn ($query) => $query->where('libro_id', $libro->id))
                    ->exists();
                if ($yaReservado) {
                    $entrada->update(['estado' => 'cancelada']);

                    continue;
                }
                if ($usuario->tieneVencidos() || $usuario->cupoRestante() <= 0) {
                    continue;
                }

                $reserva = Reserva::create([
                    'user_id' => $usuario->id,
                    'ejemplar_id' => $ejemplar->id,
                    'fecha_retiro' => today()->addDays(min(
                        (int) config('biblioteca.dias_retiro_lista_espera'),
                        (int) config('biblioteca.dias_max_reserva'),
                    )),
                    'estado' => 'activa',
                ]);

                $entrada->update([
                    'estado' => 'reservada',
                    'reserva_id' => $reserva->id,
                    'atendida_at' => now(),
                ]);

                $inventory->changeStatus(
                    $ejemplar,
                    'reservado',
                    $reserva,
                    'Reserva asignada automáticamente por lista de espera.',
                );

                return true;
            }

            return false;
        });
    }

    public function assignPending(InventoryService $inventory): void
    {
        $libroIds = DB::table('lista_espera')
            ->join('ejemplares', 'ejemplares.libro_id', '=', 'lista_espera.libro_id')
            ->where('lista_espera.estado', 'espera')
            ->where('ejemplares.estado', 'disponible')
            ->distinct()
            ->pluck('lista_espera.libro_id');

        foreach ($libroIds as $libroId) {
            $libro = Libro::find($libroId);
            if (! $libro) {
                continue;
            }

            while ($this->assignNext($libro, $inventory)) {
            }
        }
    }

    public function cancelForReservation(Reserva $reserva): void
    {
        ListaEspera::query()
            ->where('reserva_id', $reserva->id)
            ->where('estado', 'reservada')
            ->update(['estado' => 'cancelada']);
    }

    public function fulfillForReservation(Reserva $reserva): void
    {
        ListaEspera::query()
            ->where('reserva_id', $reserva->id)
            ->where('estado', 'reservada')
            ->update(['estado' => 'atendida']);
    }
}
