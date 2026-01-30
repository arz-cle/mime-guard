# MIME Guard

[![Latest Version](https://img.shields.io/packagist/v/arzou/mime-guard.svg?style=flat-square)](https://packagist.org/packages/arzou/mime-guard)
[![License](https://img.shields.io/packagist/l/arzou/mime-guard.svg?style=flat-square)](LICENSE)
[![Statamic 5+](https://img.shields.io/badge/Statamic-5.x_|_6.x-FF269E?style=flat-square)](https://statamic.com)
[![PHP 8.2+](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square)](https://php.net)

A Statamic addon for granular MIME type management. Protect your assets by controlling which file types can be uploaded, with rules at global, container, and blueprint levels.

## Features

- **Server-side validation** using magic bytes (not just file extensions)
- **Hierarchical rules**: Global → Container → Blueprint
- **Wildcard support**: `image/*`, `video/*`, etc.
- **Control Panel interface** for easy configuration
- **Logging** of rejected upload attempts
- **Multilingual**: English and French translations included

## Requirements

- Statamic 5.x or 6.x
- PHP 8.2+
- Laravel 11.x

## Installation

```bash
composer require arzou/mime-guard
```

The addon will be automatically discovered by Statamic.

### Publish Configuration (Optional)

```bash
php artisan vendor:publish --tag=mime-guard-config
```

## Configuration

### Using the Control Panel

Navigate to **CP → Tools → MIME Guard** to configure:

1. **Global Restrictions**: Select MIME types to block across all uploads
2. **Container Rules**: Define allow/deny rules per asset container
3. **Blueprint Rules**: Define allow/deny rules per collection blueprint
4. **Logging**: Enable/disable logging of rejected uploads

Settings are saved to `storage/statamic/addons/mime-guard/settings.yaml`.

### Using Configuration File

You can also configure MIME Guard via `config/mime-guard.php`:

```php
return [
    // MIME types blocked by default across all uploads
    'restricted_by_default' => [
        'application/octet-stream',
        'application/zip',
        'application/x-rar-compressed',
        'image/svg+xml',
        'application/pdf',
    ],

    // Rules per asset container
    'containers' => [
        'documents' => [
            'allow' => ['application/pdf'],
            'deny' => [],
        ],
        'images' => [
            'allow' => ['image/*'],
            'deny' => ['image/svg+xml'],
        ],
    ],

    // Rules per blueprint (format: collection::blueprint)
    'blueprints' => [
        'products::product' => [
            'allow' => ['model/stl', 'application/octet-stream'],
            'deny' => [],
        ],
    ],

    // Logging configuration
    'logging' => [
        'enabled' => true,
        'channel' => 'stack',
    ],
];
```

## How It Works

### Rule Hierarchy

Rules are evaluated in order of specificity:

1. **Global** (`restricted_by_default`) - Blocks MIME types everywhere
2. **Container** - Overrides global rules for a specific asset container
3. **Blueprint** - Overrides container rules for a specific blueprint

More specific rules always win. An `allow` rule at the container level will permit a globally restricted type.

### Wildcards

Use wildcards to match categories of MIME types:

| Pattern | Matches |
|---------|---------|
| `image/*` | All image types (jpeg, png, gif, webp, etc.) |
| `video/*` | All video types (mp4, webm, quicktime, etc.) |
| `audio/*` | All audio types (mp3, wav, ogg, etc.) |
| `application/*` | All application types |

### Server-Side Validation

MIME Guard validates files using PHP's `finfo` extension, which reads the file's magic bytes. This means:

- A `.jpg` file containing PHP code will be detected as `text/x-php`
- A renamed `.exe` file will be detected as `application/x-dosexec`
- File extensions can't be used to bypass security

## Examples

### Allow PDFs only in a specific container

```php
'containers' => [
    'documents' => [
        'allow' => ['application/pdf'],
    ],
],
```

### Block SVG files globally (XSS risk)

```php
'restricted_by_default' => [
    'image/svg+xml',
],
```

### Allow 3D models for a product blueprint

```php
'blueprints' => [
    'products::product' => [
        'allow' => [
            'model/stl',
            'model/gltf+json',
            'model/gltf-binary',
            'application/octet-stream', // STL files are often detected as this
        ],
    ],
],
```

### Allow all images except SVG

```php
'containers' => [
    'gallery' => [
        'allow' => ['image/*'],
        'deny' => ['image/svg+xml'],
    ],
],
```

## Logging

When logging is enabled, rejected uploads are logged with:

- MIME type detected
- Filename
- Container handle
- User ID

Example log entry:

```
[2025-01-28 10:30:00] local.INFO: [MIME Guard] Upload rejected {
    "mime_type": "application/zip",
    "filename": "archive.zip",
    "container": "assets",
    "user_id": 1
}
```

## Permissions

Access to the MIME Guard settings page requires the `configure mime-guard` permission. Assign this permission to roles that should manage upload restrictions.

## Common MIME Types Reference

### Images
| MIME Type | Format |
|-----------|--------|
| `image/jpeg` | JPEG |
| `image/png` | PNG |
| `image/gif` | GIF |
| `image/webp` | WebP |
| `image/svg+xml` | SVG |

### Documents
| MIME Type | Format |
|-----------|--------|
| `application/pdf` | PDF |
| `application/msword` | Word (DOC) |
| `application/vnd.openxmlformats-officedocument.wordprocessingml.document` | Word (DOCX) |

### Archives
| MIME Type | Format |
|-----------|--------|
| `application/zip` | ZIP |
| `application/x-rar-compressed` | RAR |
| `application/x-7z-compressed` | 7Z |
| `application/octet-stream` | Binary (generic) |

### 3D Models
| MIME Type | Format |
|-----------|--------|
| `model/stl` | STL |
| `application/sla` | STL (alt) |
| `model/gltf+json` | GLTF |
| `model/gltf-binary` | GLB |

### Videos
| MIME Type | Format |
|-----------|--------|
| `video/mp4` | MP4 |
| `video/webm` | WebM |
| `video/quicktime` | MOV |

## Troubleshooting

### Files are blocked but shouldn't be

1. Check the detected MIME type in the logs
2. Some files (like STL) are detected as `application/octet-stream`
3. Add the correct MIME type to your allow rules

### Container rules not working

Ensure the container handle in your config matches exactly (check for underscores vs dashes).

### Changes not taking effect

Clear your config cache:

```bash
php artisan config:clear
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

MIT License. See [LICENSE](LICENSE) for details.

## Credits

- [Clément Arzoumanian](https://github.com/arzou)
- Built for [Statamic](https://statamic.com)
