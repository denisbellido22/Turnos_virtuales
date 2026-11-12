<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTurnoPublicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:120'],
            'documento' => ['required', 'string', 'max:30'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'servicio_id' => [
                'required',
                'integer',
                Rule::exists('servicios', 'id')->where(fn ($query) => $query->where('activo', true)),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'Ingresa tu nombre completo.',
            'documento.required' => 'Ingresa tu documento de identidad.',
            'servicio_id.required' => 'Selecciona el servicio que necesitas.',
            'servicio_id.exists' => 'El servicio seleccionado no está disponible.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre' => 'nombre completo',
            'documento' => 'documento de identidad',
            'telefono' => 'teléfono',
            'servicio_id' => 'servicio',
        ];
    }
}
