<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VentanillaRequest extends FormRequest
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
            'codigo' => is_string($this->codigo) ? strtoupper(trim($this->codigo)) : $this->codigo,
        ]);
    }

    public function rules(): array
    {
        return [
            'area_id' => ['required', 'integer', Rule::exists('areas', 'id')],
            'nombre' => [
                'required',
                'string',
                'max:50',
                Rule::unique('ventanillas', 'nombre')
                    ->where(fn ($query) => $query->where('area_id', $this->integer('area_id')))
                    ->ignore($this->route('ventanilla')),
            ],
            'codigo' => [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Z0-9-]{1,20}$/',
                Rule::unique('ventanillas', 'codigo')->ignore($this->route('ventanilla')),
            ],
            'activo' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'codigo.regex' => 'El código solo admite letras mayúsculas, números y guiones.',
        ];
    }

    public function attributes(): array
    {
        return [
            'area_id' => 'área',
            'nombre' => 'nombre de la ventanilla',
            'codigo' => 'código',
            'activo' => 'estado',
        ];
    }
}
