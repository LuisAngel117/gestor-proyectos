<?php

return [
    'roles' => [
        'global' => [
            'superadmin',
            'user',
        ],
        'team' => [
            'owner',
            'admin',
            'member',
            'observer',
        ],
        'project' => [
            'owner',
            'admin',
            'member',
            'observer',
        ],
    ],
    'abilities' => [
        'view',
        'create',
        'update',
        'delete',
        'comment',
        'manageMembers',
        'transferOwnership',
        'manageDependencies',
        'registerTime',
        'attachFiles',
        'export',
        'startSprint',
        'closeSprint',
    ],
    'rules' => [
        'superadmin_overrides' => true,
        'role_storage' => 'string',
    ],
];
