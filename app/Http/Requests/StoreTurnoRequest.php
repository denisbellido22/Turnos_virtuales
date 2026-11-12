<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTurnoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->esCiudadano() === true;
    }

    public function rules(): array
    {
        return [
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
            'servicio_id.required' => 'Selecciona el servicio que necesitas.',
            'servicio_id.exists' => 'El servicio seleccionado no está disponible.',
        ];
    }

    public function attributes(): array
    {
        return ['servicio_id' => 'servicio'];
    }
}
