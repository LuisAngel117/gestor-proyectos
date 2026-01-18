<?php

return [
    'disk' => env('ATTACHMENTS_DISK', 'local'),
    'base_path' => env('ATTACHMENTS_BASE_PATH', 'attachments'),
    'max_size_mb' => (int) env('ATTACHMENTS_MAX_MB', 10),
    'allowed_extensions' => [
        'pdf',
        'png',
        'jpg',
        'jpeg',
        'docx',
    ],
    'allowed_mimetypes' => [
        'application/pdf',
        'image/png',
        'image/jpeg',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ],
];
