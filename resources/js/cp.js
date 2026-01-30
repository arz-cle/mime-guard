/**
 * MIME Guard - Client-side validation
 *
 * This script enhances the file picker to filter restricted MIME types
 * and provides client-side validation before upload.
 */

(function() {
    'use strict';

    // MIME type to extension mapping for accept attribute
    const mimeToExtensions = {
        // Images
        'image/jpeg': ['.jpg', '.jpeg'],
        'image/png': ['.png'],
        'image/gif': ['.gif'],
        'image/webp': ['.webp'],
        'image/svg+xml': ['.svg'],
        'image/bmp': ['.bmp'],
        'image/tiff': ['.tiff', '.tif'],

        // Documents
        'application/pdf': ['.pdf'],
        'application/msword': ['.doc'],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document': ['.docx'],

        // Archives
        'application/zip': ['.zip'],
        'application/x-rar-compressed': ['.rar'],
        'application/x-7z-compressed': ['.7z'],
        'application/x-tar': ['.tar'],
        'application/gzip': ['.gz'],

        // 3D Models
        'model/stl': ['.stl'],
        'application/sla': ['.stl'],
        'model/gltf+json': ['.gltf'],
        'model/gltf-binary': ['.glb'],

        // Videos
        'video/mp4': ['.mp4'],
        'video/webm': ['.webm'],
        'video/quicktime': ['.mov'],

        // Audio
        'audio/mpeg': ['.mp3'],
        'audio/wav': ['.wav'],
        'audio/ogg': ['.ogg'],
    };

    // Extension to MIME type mapping (reverse lookup)
    const extensionToMime = {};
    Object.entries(mimeToExtensions).forEach(([mime, extensions]) => {
        extensions.forEach(ext => {
            extensionToMime[ext.toLowerCase()] = mime;
        });
    });

    /**
     * Get file extension from filename
     */
    function getExtension(filename) {
        const idx = filename.lastIndexOf('.');
        return idx > 0 ? filename.substring(idx).toLowerCase() : '';
    }

    /**
     * Check if a MIME type matches a pattern (with wildcard support)
     */
    function mimeMatches(mimeType, pattern) {
        if (mimeType === pattern) {
            return true;
        }

        if (pattern.endsWith('/*')) {
            const prefix = pattern.slice(0, -1);
            return mimeType.startsWith(prefix);
        }

        return false;
    }

    /**
     * Check if a MIME type is in the restricted list
     */
    function isRestricted(mimeType, restrictedTypes) {
        return restrictedTypes.some(pattern => mimeMatches(mimeType, pattern));
    }

    /**
     * Validate a file against MIME Guard rules
     */
    function validateFile(file, config) {
        const restricted = config.restricted || [];
        const allowed = config.allowed || [];

        // Get MIME type from file or extension
        let mimeType = file.type;
        if (!mimeType) {
            const ext = getExtension(file.name);
            mimeType = extensionToMime[ext] || 'application/octet-stream';
        }

        // If explicitly allowed, it passes
        if (allowed.length > 0 && allowed.some(pattern => mimeMatches(mimeType, pattern))) {
            return { valid: true, mimeType };
        }

        // If restricted, it fails
        if (isRestricted(mimeType, restricted)) {
            return {
                valid: false,
                mimeType,
                message: `File type "${mimeType}" is not allowed.`
            };
        }

        return { valid: true, mimeType };
    }

    /**
     * Build accept attribute value from allowed MIME types
     */
    function buildAcceptAttribute(allowedTypes, restrictedTypes) {
        if (allowedTypes.length === 0) {
            // No specific allowed types, build from all types minus restricted
            const allExtensions = Object.values(mimeToExtensions).flat();
            const restrictedExtensions = new Set();

            restrictedTypes.forEach(mime => {
                const exts = mimeToExtensions[mime] || [];
                exts.forEach(ext => restrictedExtensions.add(ext));
            });

            return allExtensions
                .filter(ext => !restrictedExtensions.has(ext))
                .join(',');
        }

        // Build from allowed types
        const extensions = new Set();
        allowedTypes.forEach(mime => {
            if (mime.endsWith('/*')) {
                // Wildcard - add all extensions for that type
                const prefix = mime.slice(0, -2);
                Object.entries(mimeToExtensions).forEach(([m, exts]) => {
                    if (m.startsWith(prefix + '/')) {
                        exts.forEach(ext => extensions.add(ext));
                    }
                });
            } else {
                const exts = mimeToExtensions[mime] || [];
                exts.forEach(ext => extensions.add(ext));
            }
        });

        return Array.from(extensions).join(',');
    }

    /**
     * Initialize MIME Guard on file inputs
     */
    function initMimeGuard() {
        // Expose utility functions for Statamic components
        window.MimeGuard = {
            validateFile,
            buildAcceptAttribute,
            isRestricted,
            mimeMatches,
            mimeToExtensions,
            extensionToMime,
        };

        // Log initialization
        console.debug('[MIME Guard] Initialized');
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMimeGuard);
    } else {
        initMimeGuard();
    }
})();
