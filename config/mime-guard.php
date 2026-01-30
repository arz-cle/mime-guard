<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Restricted MIME Types
    |--------------------------------------------------------------------------
    |
    | These MIME types are blocked by default across all asset uploads.
    | They can be selectively allowed at the container, blueprint, or field level.
    |
    | Reference: https://www.iana.org/assignments/media-types/
    |
    */
    'restricted_by_default' => [
        // Generic binary (ZIP, STL, and other unrecognized binary files)
        'application/octet-stream',

        // Archives
        'application/zip',
        'application/x-rar-compressed',
        'application/x-7z-compressed',
        'application/x-tar',
        'application/gzip',

        // 3D Models
        'model/stl',
        'application/sla', // STL alias
        'model/gltf+json',
        'model/gltf-binary',

        // Vector images (XSS risk)
        'image/svg+xml',

        // Executable documents
        'application/pdf', // Can contain JavaScript

        // Videos (large file size)
        'video/mp4',
        'video/webm',
        'video/quicktime',
    ],

    /*
    |--------------------------------------------------------------------------
    | Container Rules
    |--------------------------------------------------------------------------
    |
    | Define MIME type rules per asset container.
    | Each container can have 'allow' and 'deny' arrays.
    | Wildcards are supported (e.g., 'image/*').
    |
    */
    'containers' => [
        'container_stl_allowed' => [
            'allow' => [
                'application/octet-stream', // STL files are detected as octet-stream
                'model/stl',
                'application/sla',
            ],
        ],
        // 'container_stl_not' inherits global restrictions (STL blocked)
    ],

    /*
    |--------------------------------------------------------------------------
    | Blueprint Rules
    |--------------------------------------------------------------------------
    |
    | Define MIME type rules per blueprint.
    | Format: 'collection_handle::blueprint_handle' => [...rules]
    | Wildcards are supported (e.g., 'image/*').
    |
    */
    'blueprints' => [
        // Example:
        // 'collections::products::product' => [
        //     'allow' => ['model/stl', 'application/zip'],
        //     'deny' => [],
        //     'inherit' => true,
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Enable logging of rejected upload attempts.
    | Logs include: MIME type, filename, user, container, blueprint, field.
    |
    */
    'logging' => [
        'enabled' => true,
        'channel' => env('MIME_GUARD_LOG_CHANNEL', 'stack'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Error Messages
    |--------------------------------------------------------------------------
    |
    | Customize the error messages shown to users.
    | Available placeholders: {mime_type}, {allowed_types}, {filename}
    |
    */
    'messages' => [
        'upload_rejected' => 'The file type ":mime_type" is not allowed here.',
        'allowed_types_hint' => 'Allowed types: :allowed_types',
    ],
];
