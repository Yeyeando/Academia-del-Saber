<?php

return [
    'required' => 'El campo :attribute es obligatorio.',
    'numeric' => 'El campo :attribute debe ser un número.',
    'integer' => 'El campo :attribute debe ser un número entero.',
    'date' => 'El campo :attribute debe ser una fecha válida.',
    'after' => 'El campo :attribute debe ser posterior a :date.',
    'after_or_equal' => 'El campo :attribute debe ser igual o posterior a :date.',
    'min' => [
        'numeric' => 'El campo :attribute no puede ser menor que :min.',
    ],
    'max' => [
        'string' => 'El campo :attribute no puede tener más de :max caracteres.',
    ],

    'attributes' => [
        'nombre' => 'nombre del curso',
        'precio' => 'precio',
        'vacantes' => 'vacantes',
        'foto' => 'foto',
        'fecha_inicio' => 'fecha de inicio',
        'fecha_fin' => 'fecha de fin',
    ],
];
