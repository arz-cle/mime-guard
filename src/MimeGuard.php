<?php

declare(strict_types=1);

namespace Arzou\MimeGuard;

use Arzou\MimeGuard\Rules\MimeValidator;
use Arzou\MimeGuard\Rules\RuleResolver;

/**
 * Main facade class for MIME Guard functionality.
 */
class MimeGuard
{
    protected RuleResolver $resolver;

    protected MimeValidator $validator;

    public function __construct(
        ?RuleResolver $resolver = null,
        ?MimeValidator $validator = null
    ) {
        $this->resolver = $resolver ?? new RuleResolver;
        $this->validator = $validator ?? new MimeValidator;
    }

    /**
     * Check if a file is allowed to be uploaded in the given context.
     *
     * @param  string  $filePath  Path to the file to validate
     * @param  array  $context  Context array with container, blueprint, field keys
     */
    public function isFileAllowed(string $filePath, array $context = []): bool
    {
        $mimeType = $this->validator->getMimeTypeFromContent($filePath);

        return $this->resolver->isAllowedInContext($mimeType, $context);
    }

    /**
     * Check if a MIME type is allowed in the given context.
     *
     * @param  string  $mimeType  The MIME type to check
     * @param  array  $context  Context array with container, blueprint, field keys
     */
    public function isMimeTypeAllowed(string $mimeType, array $context = []): bool
    {
        return $this->resolver->isAllowedInContext($mimeType, $context);
    }

    /**
     * Get the resolved rules for a given context.
     *
     * @param  array  $context  Context array with container, blueprint, field keys
     * @return array{restricted: array, allowed: array}
     */
    public function getRules(array $context = []): array
    {
        return $this->resolver->resolve($context);
    }

    /**
     * Get the MIME type of a file based on its content.
     */
    public function getMimeType(string $filePath): string
    {
        return $this->validator->getMimeTypeFromContent($filePath);
    }

    /**
     * Get the rule resolver instance.
     */
    public function resolver(): RuleResolver
    {
        return $this->resolver;
    }

    /**
     * Get the MIME validator instance.
     */
    public function validator(): MimeValidator
    {
        return $this->validator;
    }
}
