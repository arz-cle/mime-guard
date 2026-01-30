<?php

declare(strict_types=1);

namespace Arzou\MimeGuard\Rules;

use Illuminate\Support\Facades\Config;

class RuleResolver
{
    public function __construct(
        protected MimeValidator $validator = new MimeValidator
    ) {}

    /**
     * Resolve the effective MIME type rules for a given context.
     *
     * @param  array{container?: string, blueprint?: string, field?: array}  $context
     * @return array{restricted: array, allowed: array}
     */
    public function resolve(array $context = []): array
    {
        // Start with global restrictions
        $restricted = $this->getGlobalRestrictions();
        $allowed = [];

        // Apply container rules
        if (isset($context['container'])) {
            $containerRules = $this->getContainerRules($context['container']);
            $this->applyRules($restricted, $allowed, $containerRules);
        }

        // Apply blueprint rules
        if (isset($context['blueprint'])) {
            $blueprintRules = $this->getBlueprintRules($context['blueprint']);
            $this->applyRules($restricted, $allowed, $blueprintRules);
        }

        // Apply field rules (most specific)
        if (isset($context['field']) && is_array($context['field'])) {
            $this->applyRules($restricted, $allowed, $context['field']);
        }

        return [
            'restricted' => array_values(array_unique($restricted)),
            'allowed' => array_values(array_unique($allowed)),
        ];
    }

    /**
     * Check if a MIME type is allowed in the given context.
     */
    public function isAllowedInContext(string $mimeType, array $context = []): bool
    {
        $rules = $this->resolve($context);

        // If explicitly allowed, it's allowed
        if ($this->validator->isRestricted($mimeType, $rules['allowed'])) {
            return true;
        }

        // If restricted and not explicitly allowed, it's not allowed
        if ($this->validator->isRestricted($mimeType, $rules['restricted'])) {
            return false;
        }

        // Not restricted, so it's allowed
        return true;
    }

    /**
     * Get the list of MIME types that are allowed in the given context.
     * If no restrictions apply, returns an empty array (meaning all types allowed).
     */
    public function getAllowedTypes(array $context = []): array
    {
        $rules = $this->resolve($context);

        // If there are explicit allows, return those
        if (! empty($rules['allowed'])) {
            return $rules['allowed'];
        }

        return [];
    }

    /**
     * Get global restrictions from config.
     */
    public function getGlobalRestrictions(): array
    {
        return Config::get('mime-guard.restricted_by_default', []);
    }

    /**
     * Get container rules from config.
     */
    public function getContainerRules(string $container): array
    {
        return Config::get("mime-guard.containers.{$container}", []);
    }

    /**
     * Get blueprint rules from config.
     */
    public function getBlueprintRules(string $blueprint): array
    {
        return Config::get("mime-guard.blueprints.{$blueprint}", []);
    }

    /**
     * Apply rules (allow/deny) to the restricted and allowed lists.
     */
    protected function applyRules(array &$restricted, array &$allowed, array $rules): void
    {
        // Check inheritance flag
        if (isset($rules['inherit']) && $rules['inherit'] === false) {
            $restricted = [];
            $allowed = [];
        }

        // Apply allow rules - these types are explicitly permitted
        if (isset($rules['allow']) && is_array($rules['allow'])) {
            foreach ($rules['allow'] as $type) {
                $allowed[] = $type;
                // Remove from restricted if it was there
                $restricted = array_filter(
                    $restricted,
                    fn ($r) => ! $this->validator->matches($type, $r) && ! $this->validator->matches($r, $type)
                );
            }
        }

        // Apply deny rules - these types are added to restrictions
        if (isset($rules['deny']) && is_array($rules['deny'])) {
            foreach ($rules['deny'] as $type) {
                $restricted[] = $type;
                // Remove from allowed if it was there
                $allowed = array_filter(
                    $allowed,
                    fn ($a) => ! $this->validator->matches($type, $a) && ! $this->validator->matches($a, $type)
                );
            }
        }
    }

    /**
     * Build a context array from common parameters.
     */
    public function buildContext(?string $container = null, ?string $blueprint = null, ?array $fieldConfig = null): array
    {
        $context = [];

        if ($container !== null) {
            $context['container'] = $container;
        }

        if ($blueprint !== null) {
            $context['blueprint'] = $blueprint;
        }

        if ($fieldConfig !== null && isset($fieldConfig['mime_guard'])) {
            $context['field'] = $fieldConfig['mime_guard'];
        }

        return $context;
    }
}
