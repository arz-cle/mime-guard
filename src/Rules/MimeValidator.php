<?php

declare(strict_types=1);

namespace Arzou\MimeGuard\Rules;

use finfo;

class MimeValidator
{
    /**
     * Get the MIME type of a file based on its content (magic bytes).
     */
    public function getMimeTypeFromContent(string $filePath): string
    {
        if (! file_exists($filePath)) {
            throw new \InvalidArgumentException("File not found: {$filePath}");
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($filePath);

        return $mimeType !== false ? $mimeType : 'application/octet-stream';
    }

    /**
     * Check if a MIME type is allowed based on the allowed types list.
     * Supports wildcards (e.g., 'image/*').
     */
    public function isAllowed(string $mimeType, array $allowedTypes): bool
    {
        if (empty($allowedTypes)) {
            return true;
        }

        foreach ($allowedTypes as $allowedType) {
            if ($this->matches($mimeType, $allowedType)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a MIME type is restricted based on the restricted types list.
     * Supports wildcards (e.g., 'image/*').
     */
    public function isRestricted(string $mimeType, array $restrictedTypes): bool
    {
        foreach ($restrictedTypes as $restrictedType) {
            if ($this->matches($mimeType, $restrictedType)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a MIME type matches a pattern (with wildcard support).
     */
    public function matches(string $mimeType, string $pattern): bool
    {
        // Exact match
        if ($mimeType === $pattern) {
            return true;
        }

        // Wildcard match (e.g., 'image/*')
        if (str_ends_with($pattern, '/*')) {
            $prefix = substr($pattern, 0, -1); // 'image/'

            // Standard MIME type wildcard (image/*, video/*, model/*, etc.)
            if (str_starts_with($mimeType, $prefix)) {
                return true;
            }

            // Custom category wildcards that map to non-standard prefixes
            $categoryMapping = $this->getCategoryMapping();
            $category = substr($pattern, 0, -2); // 'document', 'archive', etc.

            if (isset($categoryMapping[$category])) {
                return in_array($mimeType, $categoryMapping[$category], true);
            }
        }

        return false;
    }

    /**
     * Get mapping of custom categories to their MIME types.
     * Used for wildcards like 'document/*' and 'archive/*'.
     */
    protected function getCategoryMapping(): array
    {
        return [
            'document' => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'text/plain',
                'text/rtf',
                'application/rtf',
            ],
            'archive' => [
                'application/zip',
                'application/x-rar-compressed',
                'application/vnd.rar',
                'application/x-7z-compressed',
                'application/x-tar',
                'application/gzip',
                'application/x-gzip',
                'application/x-bzip2',
                'application/octet-stream',
            ],
        ];
    }

    /**
     * Filter a list of MIME types by removing restricted ones.
     */
    public function filterRestricted(array $mimeTypes, array $restrictedTypes): array
    {
        return array_values(array_filter(
            $mimeTypes,
            fn (string $mimeType) => ! $this->isRestricted($mimeType, $restrictedTypes)
        ));
    }

    /**
     * Get common file extensions for a MIME type.
     */
    public function getExtensionsForMimeType(string $mimeType): array
    {
        $mapping = $this->getMimeToExtensionMapping();

        return $mapping[$mimeType] ?? [];
    }

    /**
     * Get MIME type to extension mapping.
     */
    protected function getMimeToExtensionMapping(): array
    {
        return [
            // Images
            'image/jpeg' => ['jpg', 'jpeg'],
            'image/png' => ['png'],
            'image/gif' => ['gif'],
            'image/webp' => ['webp'],
            'image/svg+xml' => ['svg'],
            'image/bmp' => ['bmp'],
            'image/tiff' => ['tiff', 'tif'],

            // Documents
            'application/pdf' => ['pdf'],
            'application/msword' => ['doc'],
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
            'application/vnd.ms-excel' => ['xls'],
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['xlsx'],

            // Archives
            'application/zip' => ['zip'],
            'application/x-rar-compressed' => ['rar'],
            'application/x-7z-compressed' => ['7z'],
            'application/x-tar' => ['tar'],
            'application/gzip' => ['gz'],

            // 3D Models
            'model/stl' => ['stl'],
            'application/sla' => ['stl'],
            'model/gltf+json' => ['gltf'],
            'model/gltf-binary' => ['glb'],

            // Videos
            'video/mp4' => ['mp4'],
            'video/webm' => ['webm'],
            'video/quicktime' => ['mov'],

            // Audio
            'audio/mpeg' => ['mp3'],
            'audio/wav' => ['wav'],
            'audio/ogg' => ['ogg'],
        ];
    }
}
