<?php

return [
    'projects' => [
        'statuses' => [
            'planificacion' => 'Proyecto creado pero aún no iniciado formalmente.',
            'en_progreso' => 'Proyecto en ejecución.',
            'en_espera' => 'Proyecto pausado temporalmente.',
            'completado' => 'Proyecto terminado.',
            'cancelado' => 'Proyecto cancelado.',
            'archivado' => 'Proyecto cerrado y archivado (solo consulta).',
        ],
        'priorities' => [
            'baja' => 'Prioridad baja.',
            'media' => 'Prioridad media (por defecto).',
            'alta' => 'Prioridad alta.',
            'urgente' => 'Prioridad urgente.',
        ],
        'transitions' => [
            'planificacion' => ['en_progreso', 'cancelado', 'archivado'],
            'en_progreso' => ['en_espera', 'completado', 'cancelado'],
            'en_espera' => ['en_progreso', 'cancelado', 'archivado'],
            'completado' => ['archivado'],
            'cancelado' => ['archivado'],
            'archivado' => [],
        ],
    ],
];
