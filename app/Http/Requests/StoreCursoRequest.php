<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCursoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        return [
            'nombre' => 'required|string|max:255|min:3',
            'precio' => 'required|numeric|min:0',
            'vacantes' => 'required|integer|min:0',
            'fecha_inicio' => 'required|date|after_or_equal:today',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'foto'   => 'nullable|image|max:2048',
            'categoria_id' => 'nullable|exists:categorias,id',
        ];
    }
/*
    public function messages(): array {
        return [

            // NOMBRE
            'nombre.required' => 'El nombre del curso es obligatorio.',
            'nombre.string'   => 'El nombre del curso debe ser un texto válido.',
            'nombre.max'      => 'El nombre del curso no puede superar los 255 caracteres.',
            'nombre.min'      => 'El nombre del curso no puede tener menos de 4 caracteres.',

            // PRECIO
            'precio.required' => 'El precio es obligatorio.',
            'precio.numeric'  => 'El precio debe ser un número válido (se permiten decimales).',
            'precio.min'      => 'El precio no puede ser negativo.',

            // VACANTES
            'vacantes.required' => 'El número de vacantes es obligatorio.',
            'vacantes.integer'  => 'Las vacantes deben ser un número entero.',
            'vacantes.min'      => 'Las vacantes no pueden ser un valor negativo.',

            // FECHA INICIO
            'fecha_inicio.required'        => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.date'            => 'La fecha de inicio debe ser una fecha válida.',
            'fecha_inicio.after_or_equal'  => 'La fecha de inicio no puede ser anterior a hoy.',

            // FECHA FIN
            'fecha_fin.required' => 'La fecha de fin es obligatoria.',
            'fecha_fin.date'     => 'La fecha de fin debe ser una fecha válida.',
            'fecha_fin.after'    => 'La fecha de fin debe ser posterior a la fecha de inicio.',

            //FOTO
            'foto.image'      => 'El archivo debe ser una imagen (jpg, png, gif, etc.).',
            'foto.max'        => 'La imagen no puede pesar más de 2 MB.',
        ];
    }
        */

}
