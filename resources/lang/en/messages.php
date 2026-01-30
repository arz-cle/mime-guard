<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | MIME Guard Messages
    |--------------------------------------------------------------------------
    |
    | These messages are used throughout the MIME Guard addon.
    | Available placeholders: :mime_type, :allowed_types, :filename
    |
    */

    'upload_rejected' => 'The file type ":mime_type" is not allowed here.',
    'allowed_types_hint' => 'Allowed types: :allowed_types',

    // Control Panel
    'settings_title' => 'MIME Guard Settings',
    'settings_saved' => 'Settings saved successfully.',
    'permission_configure' => 'Configure MIME Guard',

    // Global Restrictions
    'global_restrictions' => 'Global Restrictions',
    'global_restrictions_help' => 'These MIME types are blocked by default across all asset uploads.',
    'restricted_mime_types' => 'Restricted MIME Types',
    'check_to_block' => 'Check to block this file type',
    'toggle_all' => 'Toggle all',
    'custom_mime_types' => 'Custom MIME Types',
    'custom_mime_types_help' => 'Add additional MIME types (one per line)',

    // Container Rules
    'container_rules' => 'Container Rules',
    'container_rules_help' => 'Override global restrictions for each asset container.',
    'container_types_help' => 'Check types to allow in this container (overrides global restrictions).',
    'no_containers' => 'No asset containers found.',
    'create_container' => 'Create Container',

    // Blueprint Rules
    'blueprint_rules' => 'Blueprint Rules',
    'blueprint_rules_help' => 'Override rules for each collection blueprint.',
    'blueprint_types_help' => 'Check types to allow in this blueprint (overrides container rules).',
    'no_blueprints' => 'No blueprints found.',

    // Logging
    'logging' => 'Logging',
    'logging_enabled' => 'Enable logging of rejected uploads',
    'logging_help' => 'Logs include: MIME type, filename, user, and container.',

    // Actions
    'save_settings' => 'Save Settings',
    'configured' => 'Configured',

    // Help
    'help_title' => 'How it works',
    'help_hierarchy' => 'Rule hierarchy:',
    'help_hierarchy_desc' => 'Global → Container → Blueprint → Field. More specific rules override general ones.',
    'help_wildcards' => 'Wildcards:',
    'help_wildcards_desc' => 'Use image/*, video/*, etc. to match all types in a category.',

    // Validation
    'file_type_not_allowed' => 'File type not allowed',
    'file_rejected' => 'The file ":filename" was rejected because its type (:mime_type) is not allowed.',
];
