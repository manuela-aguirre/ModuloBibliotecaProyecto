<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->esAdministrador() ?? false;
    }

    public function rules(): array
    {
        return [
            'ejemplar_id' => ['required', 'integer', 'exists:ejemplares,id'],
            'estado' => ['required', Rule::in(['disponible', 'danado', 'baja'])],
            'motivo' => ['required', 'string', 'min:10', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'ejemplar_id.required' => 'Seleccione un ejemplar.',
            'estado.required' => 'Seleccione el estado contado.',
            'motivo.required' => 'El motivo del ajuste es obligatorio.',
            'motivo.min' => 'El motivo del ajuste debe tener al menos 10 caracteres.',
            'motivo.max' => 'El motivo del ajuste no puede superar los 255 caracteres.',
        ];
    }
}
