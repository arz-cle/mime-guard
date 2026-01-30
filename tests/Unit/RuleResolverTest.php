<?php

declare(strict_types=1);

use Arzou\MimeGuard\Rules\RuleResolver;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    $this->resolver = new RuleResolver;

    // Reset config before each test
    Config::set('mime-guard.restricted_by_default', []);
    Config::set('mime-guard.containers', []);
    Config::set('mime-guard.blueprints', []);
});

describe('RuleResolver', function () {
    describe('getGlobalRestrictions', function () {
        it('returns configured global restrictions', function () {
            Config::set('mime-guard.restricted_by_default', [
                'application/zip',
                'application/pdf',
            ]);

            expect($this->resolver->getGlobalRestrictions())->toBe([
                'application/zip',
                'application/pdf',
            ]);
        });

        it('returns empty array when no restrictions configured', function () {
            expect($this->resolver->getGlobalRestrictions())->toBe([]);
        });
    });

    describe('resolve', function () {
        it('returns global restrictions when no context provided', function () {
            Config::set('mime-guard.restricted_by_default', [
                'application/zip',
                'image/svg+xml',
            ]);

            $result = $this->resolver->resolve();

            expect($result['restricted'])->toBe(['application/zip', 'image/svg+xml']);
            expect($result['allowed'])->toBe([]);
        });

        it('applies container allow rules', function () {
            Config::set('mime-guard.restricted_by_default', [
                'application/zip',
                'application/pdf',
            ]);
            Config::set('mime-guard.containers.documents', [
                'allow' => ['application/pdf'],
            ]);

            $result = $this->resolver->resolve(['container' => 'documents']);

            expect($result['allowed'])->toContain('application/pdf');
            expect($result['restricted'])->not->toContain('application/pdf');
        });

        it('applies container deny rules', function () {
            Config::set('mime-guard.restricted_by_default', []);
            Config::set('mime-guard.containers.strict', [
                'deny' => ['image/svg+xml'],
            ]);

            $result = $this->resolver->resolve(['container' => 'strict']);

            expect($result['restricted'])->toContain('image/svg+xml');
        });

        it('applies blueprint rules over container rules', function () {
            Config::set('mime-guard.restricted_by_default', ['application/zip']);
            Config::set('mime-guard.containers.assets', [
                'deny' => ['application/pdf'],
            ]);
            Config::set('mime-guard.blueprints.products::product', [
                'allow' => ['application/zip', 'application/pdf'],
            ]);

            $result = $this->resolver->resolve([
                'container' => 'assets',
                'blueprint' => 'products::product',
            ]);

            expect($result['allowed'])->toContain('application/zip');
            expect($result['allowed'])->toContain('application/pdf');
        });

        it('respects inherit:false flag', function () {
            Config::set('mime-guard.restricted_by_default', [
                'application/zip',
                'application/pdf',
            ]);
            Config::set('mime-guard.containers.custom', [
                'inherit' => false,
                'deny' => ['image/svg+xml'],
            ]);

            $result = $this->resolver->resolve(['container' => 'custom']);

            expect($result['restricted'])->toBe(['image/svg+xml']);
            expect($result['restricted'])->not->toContain('application/zip');
        });
    });

    describe('isAllowedInContext', function () {
        it('blocks globally restricted types', function () {
            Config::set('mime-guard.restricted_by_default', ['application/zip']);

            expect($this->resolver->isAllowedInContext('application/zip', []))->toBeFalse();
        });

        it('allows non-restricted types', function () {
            Config::set('mime-guard.restricted_by_default', ['application/zip']);

            expect($this->resolver->isAllowedInContext('image/jpeg', []))->toBeTrue();
        });

        it('allows explicitly permitted types even if globally restricted', function () {
            Config::set('mime-guard.restricted_by_default', ['application/zip']);
            Config::set('mime-guard.containers.archives', [
                'allow' => ['application/zip'],
            ]);

            expect($this->resolver->isAllowedInContext('application/zip', [
                'container' => 'archives',
            ]))->toBeTrue();
        });

        it('blocks types denied at container level', function () {
            Config::set('mime-guard.containers.strict', [
                'deny' => ['image/svg+xml'],
            ]);

            expect($this->resolver->isAllowedInContext('image/svg+xml', [
                'container' => 'strict',
            ]))->toBeFalse();
        });

        it('supports wildcard restrictions', function () {
            Config::set('mime-guard.restricted_by_default', ['video/*']);

            expect($this->resolver->isAllowedInContext('video/mp4', []))->toBeFalse();
            expect($this->resolver->isAllowedInContext('video/webm', []))->toBeFalse();
            expect($this->resolver->isAllowedInContext('image/jpeg', []))->toBeTrue();
        });
    });

    describe('buildContext', function () {
        it('builds context with container', function () {
            $context = $this->resolver->buildContext('assets');

            expect($context)->toBe(['container' => 'assets']);
        });

        it('builds context with container and blueprint', function () {
            $context = $this->resolver->buildContext('assets', 'products::product');

            expect($context)->toBe([
                'container' => 'assets',
                'blueprint' => 'products::product',
            ]);
        });

        it('builds context with field config', function () {
            $fieldConfig = [
                'type' => 'assets',
                'mime_guard' => [
                    'allow' => ['application/pdf'],
                ],
            ];

            $context = $this->resolver->buildContext('assets', null, $fieldConfig);

            expect($context)->toBe([
                'container' => 'assets',
                'field' => ['allow' => ['application/pdf']],
            ]);
        });

        it('ignores field config without mime_guard key', function () {
            $fieldConfig = [
                'type' => 'assets',
            ];

            $context = $this->resolver->buildContext('assets', null, $fieldConfig);

            expect($context)->toBe(['container' => 'assets']);
        });
    });

    describe('hierarchy', function () {
        it('follows Global → Container → Blueprint → Field hierarchy', function () {
            // Global restricts ZIP
            Config::set('mime-guard.restricted_by_default', ['application/zip']);

            // Container allows ZIP
            Config::set('mime-guard.containers.uploads', [
                'allow' => ['application/zip'],
            ]);

            // Blueprint denies ZIP again
            Config::set('mime-guard.blueprints.pages::page', [
                'deny' => ['application/zip'],
            ]);

            // Field context overrides to allow
            $fieldRules = ['allow' => ['application/zip']];

            // Without field context - should be blocked by blueprint
            expect($this->resolver->isAllowedInContext('application/zip', [
                'container' => 'uploads',
                'blueprint' => 'pages::page',
            ]))->toBeFalse();

            // With field context - should be allowed
            expect($this->resolver->isAllowedInContext('application/zip', [
                'container' => 'uploads',
                'blueprint' => 'pages::page',
                'field' => $fieldRules,
            ]))->toBeTrue();
        });
    });
});
