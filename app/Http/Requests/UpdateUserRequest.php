<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $usuarioId = $this->route('usuario'); // obtenemos el id del usuario que viene desde la ruta

        return [
            'username' => "required|string|max:50|unique:users,username,{$usuarioId}",
            'is_admin' => 'required|boolean',
            'estado' => 'required|boolean',
            'fecha_expiracion' => 'nullable|date|after_or_equal:today',
        ];
    }
}
