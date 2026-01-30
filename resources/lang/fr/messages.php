<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Messages MIME Guard
    |--------------------------------------------------------------------------
    |
    | Ces messages sont utilisés dans l'addon MIME Guard.
    | Placeholders disponibles : :mime_type, :allowed_types, :filename
    |
    */

    'upload_rejected' => 'Le type de fichier ":mime_type" n\'est pas autorisé ici.',
    'allowed_types_hint' => 'Types acceptés : :allowed_types',

    // Control Panel
    'settings_title' => 'Paramètres MIME Guard',
    'settings_saved' => 'Paramètres enregistrés avec succès.',
    'permission_configure' => 'Configurer MIME Guard',

    // Global Restrictions
    'global_restrictions' => 'Restrictions globales',
    'global_restrictions_help' => 'Ces types MIME sont bloqués par défaut pour tous les uploads.',
    'restricted_mime_types' => 'Types MIME restreints',
    'check_to_block' => 'Cocher pour bloquer ce type de fichier',
    'toggle_all' => 'Tout sélectionner',
    'custom_mime_types' => 'Types MIME personnalisés',
    'custom_mime_types_help' => 'Ajoutez des types MIME supplémentaires (un par ligne)',

    // Container Rules
    'container_rules' => 'Règles par container',
    'container_rules_help' => 'Remplacez les restrictions globales pour chaque container.',
    'container_types_help' => 'Cochez les types à autoriser dans ce container (remplace les restrictions globales).',
    'no_containers' => 'Aucun container trouvé.',
    'create_container' => 'Créer un container',

    // Blueprint Rules
    'blueprint_rules' => 'Règles par blueprint',
    'blueprint_rules_help' => 'Remplacez les règles pour chaque blueprint de collection.',
    'blueprint_types_help' => 'Cochez les types à autoriser dans ce blueprint (remplace les règles du container).',
    'no_blueprints' => 'Aucun blueprint trouvé.',

    // Logging
    'logging' => 'Journalisation',
    'logging_enabled' => 'Activer la journalisation des uploads refusés',
    'logging_help' => 'Les logs incluent : type MIME, nom du fichier, utilisateur et container.',

    // Actions
    'save_settings' => 'Enregistrer',
    'configured' => 'Configuré',

    // Help
    'help_title' => 'Comment ça fonctionne',
    'help_hierarchy' => 'Hiérarchie des règles :',
    'help_hierarchy_desc' => 'Global → Container → Blueprint → Field. Les règles plus spécifiques remplacent les générales.',
    'help_wildcards' => 'Caractères génériques :',
    'help_wildcards_desc' => 'Utilisez image/*, video/*, etc. pour correspondre à tous les types d\'une catégorie.',

    // Validation
    'file_type_not_allowed' => 'Type de fichier non autorisé',
    'file_rejected' => 'Le fichier ":filename" a été refusé car son type (:mime_type) n\'est pas autorisé.',
];
