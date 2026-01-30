<?php

declare(strict_types=1);

use Arzou\MimeGuard\MimeGuard;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    $this->mimeGuard = new MimeGuard;
});

describe('MimeGuard facade', function () {
    it('validates MIME type against global restrictions', function () {
        Config::set('mime-guard.restricted_by_default', ['application/zip', 'image/svg+xml']);
        Config::set('mime-guard.containers', []);
        Config::set('mime-guard.blueprints', []);

        expect($this->mimeGuard->isMimeTypeAllowed('application/zip'))->toBeFalse();
        expect($this->mimeGuard->isMimeTypeAllowed('image/jpeg'))->toBeTrue();
    });

    it('allows restricted types when explicitly permitted', function () {
        Config::set('mime-guard.restricted_by_default', ['application/zip']);
        Config::set('mime-guard.containers', []);
        Config::set('mime-guard.blueprints', [
            'products' => [
                'allow' => ['application/zip'],
            ],
        ]);

        expect($this->mimeGuard->isMimeTypeAllowed('application/zip', ['blueprint' => 'products']))->toBeTrue();
        expect($this->mimeGuard->isMimeTypeAllowed('application/zip'))->toBeFalse();
    });

    it('returns resolved rules for context', function () {
        Config::set('mime-guard.restricted_by_default', ['application/zip']);
        Config::set('mime-guard.containers', []);
        Config::set('mime-guard.blueprints', [
            'products' => [
                'allow' => ['application/zip', 'model/stl'],
            ],
        ]);

        $rules = $this->mimeGuard->getRules(['blueprint' => 'products']);

        expect($rules['allowed'])->toContain('application/zip');
        expect($rules['allowed'])->toContain('model/stl');
    });
});

describe('hierarchical rule resolution', function () {
    it('field rules override blueprint rules', function () {
        Config::set('mime-guard.restricted_by_default', []);
        Config::set('mime-guard.containers', []);
        Config::set('mime-guard.blueprints', [
            'products' => [
                'allow' => ['image/*'],
            ],
        ]);

        $context = [
            'blueprint' => 'products',
            'field' => [
                'deny' => ['image/svg+xml'],
            ],
        ];

        expect($this->mimeGuard->isMimeTypeAllowed('image/jpeg', $context))->toBeTrue();
        expect($this->mimeGuard->isMimeTypeAllowed('image/svg+xml', $context))->toBeFalse();
    });

    it('container rules apply to all assets in container', function () {
        Config::set('mime-guard.restricted_by_default', ['application/pdf']);
        Config::set('mime-guard.containers', [
            'documents' => [
                'allow' => ['application/pdf'],
            ],
        ]);
        Config::set('mime-guard.blueprints', []);

        expect($this->mimeGuard->isMimeTypeAllowed('application/pdf', ['container' => 'documents']))->toBeTrue();
        expect($this->mimeGuard->isMimeTypeAllowed('application/pdf', ['container' => 'images']))->toBeFalse();
    });

    it('inherit false ignores parent restrictions', function () {
        Config::set('mime-guard.restricted_by_default', ['application/zip', 'video/mp4']);
        Config::set('mime-guard.containers', []);
        Config::set('mime-guard.blueprints', []);

        $context = [
            'field' => [
                'inherit' => false,
                'deny' => ['image/svg+xml'],
            ],
        ];

        // Original restrictions should be ignored
        expect($this->mimeGuard->isMimeTypeAllowed('application/zip', $context))->toBeTrue();
        expect($this->mimeGuard->isMimeTypeAllowed('video/mp4', $context))->toBeTrue();

        // Only field-level deny should apply
        expect($this->mimeGuard->isMimeTypeAllowed('image/svg+xml', $context))->toBeFalse();
    });
});
