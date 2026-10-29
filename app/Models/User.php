<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

#[Fillable(['name', 'email', 'tipo_identificacion', 'numero_identificacion', 'telefono', 'carrera', 'semestre', 'datos_aceptados_at', 'rol', 'limite_prestamos', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function esAdministrador(): bool
    {
        return $this->rol === 'administrador';
    }

    public function prestamos()
    {
        return $this->hasMany(Prestamo::class);
    }

    public function reservas()
    {
        return $this->hasMany(Reserva::class);
    }

    /** Reservas activas + ejemplares que tiene prestados ahora. */
    public function cupoUsado(): int
    {
        $reservas = $this->reservas()->where('estado', 'activa')->count();

        $prestados = DB::table('prestamo_ejemplar')
            ->join('prestamos', 'prestamos.id', '=', 'prestamo_ejemplar.prestamo_id')
            ->where('prestamos.user_id', $this->id)
            ->where('prestamo_ejemplar.estado', 'prestado')
            ->count();

        return $reservas + $prestados;
    }

   public function cupoRestante(): int
{
    return max(0, ($this->limite_prestamos ?? 3) - $this->cupoUsado());
}

    public function tieneVencidos(): bool
    {
        return $this->prestamos()->vencidos()->exists();
    }
}