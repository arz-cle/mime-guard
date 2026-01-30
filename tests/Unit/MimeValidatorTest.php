<?php

declare(strict_types=1);

use Arzou\MimeGuard\Rules\MimeValidator;

beforeEach(function () {
    $this->validator = new MimeValidator;
});

describe('MimeValidator', function () {
    describe('matches', function () {
        it('matches exact MIME types', function () {
            expect($this->validator->matches('image/jpeg', 'image/jpeg'))->toBeTrue();
            expect($this->validator->matches('image/png', 'image/jpeg'))->toBeFalse();
        });

        it('matches wildcard patterns', function () {
            expect($this->validator->matches('image/jpeg', 'image/*'))->toBeTrue();
            expect($this->validator->matches('image/png', 'image/*'))->toBeTrue();
            expect($this->validator->matches('image/gif', 'image/*'))->toBeTrue();
            expect($this->validator->matches('video/mp4', 'image/*'))->toBeFalse();
        });

        it('matches video wildcards', function () {
            expect($this->validator->matches('video/mp4', 'video/*'))->toBeTrue();
            expect($this->validator->matches('video/webm', 'video/*'))->toBeTrue();
            expect($this->validator->matches('image/jpeg', 'video/*'))->toBeFalse();
        });

        it('matches application wildcards', function () {
            expect($this->validator->matches('application/pdf', 'application/*'))->toBeTrue();
            expect($this->validator->matches('application/zip', 'application/*'))->toBeTrue();
            expect($this->validator->matches('image/png', 'application/*'))->toBeFalse();
        });

        it('matches document category wildcard', function () {
            expect($this->validator->matches('application/pdf', 'document/*'))->toBeTrue();
            expect($this->validator->matches('application/msword', 'document/*'))->toBeTrue();
            expect($this->validator->matches('application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'document/*'))->toBeTrue();
            expect($this->validator->matches('image/jpeg', 'document/*'))->toBeFalse();
            expect($this->validator->matches('application/zip', 'document/*'))->toBeFalse();
        });

        it('matches archive category wildcard', function () {
            expect($this->validator->matches('application/zip', 'archive/*'))->toBeTrue();
            expect($this->validator->matches('application/x-rar-compressed', 'archive/*'))->toBeTrue();
            expect($this->validator->matches('application/x-7z-compressed', 'archive/*'))->toBeTrue();
            expect($this->validator->matches('application/octet-stream', 'archive/*'))->toBeTrue();
            expect($this->validator->matches('image/jpeg', 'archive/*'))->toBeFalse();
            expect($this->validator->matches('application/pdf', 'archive/*'))->toBeFalse();
        });

        it('matches model wildcards', function () {
            expect($this->validator->matches('model/stl', 'model/*'))->toBeTrue();
            expect($this->validator->matches('model/gltf+json', 'model/*'))->toBeTrue();
            expect($this->validator->matches('model/gltf-binary', 'model/*'))->toBeTrue();
            expect($this->validator->matches('image/jpeg', 'model/*'))->toBeFalse();
        });
    });

    describe('isAllowed', function () {
        it('returns true when allowed types list is empty', function () {
            expect($this->validator->isAllowed('image/jpeg', []))->toBeTrue();
            expect($this->validator->isAllowed('application/pdf', []))->toBeTrue();
        });

        it('returns true when MIME type is in allowed list', function () {
            $allowed = ['image/jpeg', 'image/png', 'application/pdf'];
            expect($this->validator->isAllowed('image/jpeg', $allowed))->toBeTrue();
            expect($this->validator->isAllowed('image/png', $allowed))->toBeTrue();
            expect($this->validator->isAllowed('application/pdf', $allowed))->toBeTrue();
        });

        it('returns false when MIME type is not in allowed list', function () {
            $allowed = ['image/jpeg', 'image/png'];
            expect($this->validator->isAllowed('application/pdf', $allowed))->toBeFalse();
            expect($this->validator->isAllowed('video/mp4', $allowed))->toBeFalse();
        });

        it('supports wildcards in allowed list', function () {
            $allowed = ['image/*'];
            expect($this->validator->isAllowed('image/jpeg', $allowed))->toBeTrue();
            expect($this->validator->isAllowed('image/png', $allowed))->toBeTrue();
            expect($this->validator->isAllowed('image/svg+xml', $allowed))->toBeTrue();
            expect($this->validator->isAllowed('video/mp4', $allowed))->toBeFalse();
        });
    });

    describe('isRestricted', function () {
        it('returns false when restricted types list is empty', function () {
            expect($this->validator->isRestricted('image/jpeg', []))->toBeFalse();
        });

        it('returns true when MIME type is in restricted list', function () {
            $restricted = ['application/zip', 'application/pdf'];
            expect($this->validator->isRestricted('application/zip', $restricted))->toBeTrue();
            expect($this->validator->isRestricted('application/pdf', $restricted))->toBeTrue();
        });

        it('returns false when MIME type is not in restricted list', function () {
            $restricted = ['application/zip', 'application/pdf'];
            expect($this->validator->isRestricted('image/jpeg', $restricted))->toBeFalse();
        });

        it('supports wildcards in restricted list', function () {
            $restricted = ['video/*'];
            expect($this->validator->isRestricted('video/mp4', $restricted))->toBeTrue();
            expect($this->validator->isRestricted('video/webm', $restricted))->toBeTrue();
            expect($this->validator->isRestricted('image/jpeg', $restricted))->toBeFalse();
        });
    });

    describe('filterRestricted', function () {
        it('removes restricted types from list', function () {
            $types = ['image/jpeg', 'application/zip', 'image/png', 'application/pdf'];
            $restricted = ['application/zip', 'application/pdf'];

            $filtered = $this->validator->filterRestricted($types, $restricted);

            expect($filtered)->toBe(['image/jpeg', 'image/png']);
        });

        it('handles wildcard restrictions', function () {
            $types = ['image/jpeg', 'video/mp4', 'image/png', 'video/webm'];
            $restricted = ['video/*'];

            $filtered = $this->validator->filterRestricted($types, $restricted);

            expect($filtered)->toBe(['image/jpeg', 'image/png']);
        });
    });

    describe('getExtensionsForMimeType', function () {
        it('returns extensions for known MIME types', function () {
            expect($this->validator->getExtensionsForMimeType('image/jpeg'))->toBe(['jpg', 'jpeg']);
            expect($this->validator->getExtensionsForMimeType('image/png'))->toBe(['png']);
            expect($this->validator->getExtensionsForMimeType('application/pdf'))->toBe(['pdf']);
        });

        it('returns empty array for unknown MIME types', function () {
            expect($this->validator->getExtensionsForMimeType('unknown/type'))->toBe([]);
        });
    });
});
