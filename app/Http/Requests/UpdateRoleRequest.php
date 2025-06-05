<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
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
        $roleId = $this->route('role'); // obtenemos el id del rol que viene desde la ruta

        return [
            'name' => "required|string|max:255|unique:roles,name,{$roleId}",
            'descripcion' => 'required|string|max:255',
            'guard_name' => 'nullable|string|max:50',
        ];
    }
}
