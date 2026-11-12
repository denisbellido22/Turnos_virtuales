<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->esAdministrador() === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'activo' => $this->boolean('activo'),
            'nombre' => is_string($this->nombre) ? trim($this->nombre) : $this->nombre,
        ]);
    }

    public function rules(): array
    {
        return [
            'nombre' => [
                'required',
                'string',
                'max:100',
                Rule::unique('areas', 'nombre')->ignore($this->route('area')),
            ],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'activo' => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre' => 'nombre del área',
            'descripcion' => 'descripción',
            'activo' => 'estado',
        ];
    }
}
