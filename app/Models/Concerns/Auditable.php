<?php

namespace App\Models\Concerns;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

trait Auditable
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logExcept($this->auditExcept ?? [])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName($this->auditLog ?? $this->getTable())
            ->setDescriptionForEvent(fn (string $event) => match ($event) {
                'created' => 'creado',
                'updated' => 'actualizado',
                'deleted' => 'eliminado',
                'restored' => 'restaurado',
                default => $event,
            });
    }
}
