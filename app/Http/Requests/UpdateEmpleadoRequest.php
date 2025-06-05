<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmpleadoRequest extends FormRequest
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
        $empleadoId = $this->route('empleado');// obtenemos el id del empleado que viene desde la ruta

        return [
            'primer_nombre' => 'required|string|max:255',
            'segundo_nombre' => 'nullable|string|max:255',
            'primer_apellido' => 'required|string|max:255',
            'segundo_apellido' => 'required|string|max:255',
            'tipo_identificacion' => 'required|string|max:255',
            'numero_identificacion' => "required|string|max:11|unique:empleados,numero_identificacion,{$empleadoId}",
            'correo' => "required|email|max:255|unique:empleados,correo,{$empleadoId}",
            'telefono' => 'nullable|string|max:15',
        ];
    }
}
