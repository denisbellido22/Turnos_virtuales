<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServicioRequest extends FormRequest
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
            'prefijo' => is_string($this->prefijo) ? strtoupper(trim($this->prefijo)) : $this->prefijo,
        ]);
    }

    public function rules(): array
    {
        return [
            'area_id' => ['required', 'integer', Rule::exists('areas', 'id')],
            'nombre' => [
                'required',
                'string',
                'max:100',
                Rule::unique('servicios', 'nombre')
                    ->where(fn ($query) => $query->where('area_id', $this->integer('area_id')))
                    ->ignore($this->route('servicio')),
            ],
            'prefijo' => [
                'required',
                'string',
                'max:5',
                'regex:/^[A-Z0-9]{2,5}$/',
                Rule::unique('servicios', 'prefijo')->ignore($this->route('servicio')),
            ],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'activo' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'prefijo.regex' => 'El prefijo debe tener entre 2 y 5 caracteres: letras mayúsculas o números.',
        ];
    }

    public function attributes(): array
    {
        return [
            'area_id' => 'área',
            'nombre' => 'nombre del servicio',
            'prefijo' => 'prefijo',
            'descripcion' => 'descripción',
            'activo' => 'estado',
        ];
    }
}
