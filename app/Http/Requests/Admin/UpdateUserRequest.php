<?php

namespace App\Http\Requests\Admin;

use App\Models\Rol;
use App\Models\User;
use App\Models\Ventanilla;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->esAdministrador() === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'activo' => $this->boolean('activo'),
            'email' => is_string($this->email) ? strtolower(trim($this->email)) : $this->email,
        ]);
    }

    public function rules(): array
    {
        /** @var User $usuario */
        $usuario = $this->route('usuario');

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:150', Rule::unique('users', 'email')->ignore($usuario->id)],
            'password' => ['nullable', 'string', 'confirmed', 'min:8'],
            'rol_id' => ['required', Rule::exists('roles', 'id')->whereIn('nombre', [Rol::ADMINISTRADOR, Rol::EMPLEADO])],
            'area_id' => ['nullable', 'integer', Rule::exists('areas', 'id')],
            'ventanilla_id' => ['nullable', 'integer', Rule::exists('ventanillas', 'id')],
            'activo' => ['boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            /** @var User $usuario */
            $usuario = $this->route('usuario');
            $rol = Rol::query()->find($this->integer('rol_id'));

            if ($usuario->is($this->user())) {
                if ((int) $this->integer('rol_id') !== (int) $usuario->rol_id) {
                    $validator->errors()->add('rol_id', 'No puedes cambiar tu propio rol.');
                }

                if (! $this->boolean('activo')) {
                    $validator->errors()->add('activo', 'No puedes desactivar tu propia cuenta.');
                }
            }

            if ($rol?->nombre !== Rol::EMPLEADO) {
                return;
            }

            if (! $this->input('area_id')) {
                $validator->errors()->add('area_id', 'Selecciona un área para el empleado.');
            }

            if (! $this->input('ventanilla_id')) {
                $validator->errors()->add('ventanilla_id', 'Asigna una ventanilla al empleado.');
            }

            if ($this->input('area_id') && $this->input('ventanilla_id')) {
                $valida = Ventanilla::query()
                    ->whereKey($this->integer('ventanilla_id'))
                    ->where('area_id', $this->integer('area_id'))
                    ->exists();

                if (! $valida) {
                    $validator->errors()->add('ventanilla_id', 'La ventanilla no pertenece al área seleccionada.');
                }
            }
        });
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'email' => 'correo electrónico',
            'password' => 'contraseña',
            'rol_id' => 'rol',
            'area_id' => 'área',
            'ventanilla_id' => 'ventanilla',
            'activo' => 'estado',
        ];
    }
}
