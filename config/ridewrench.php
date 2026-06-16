<?php

return [
    'languages' => [
        'en' => [
            'label' => 'English',
            'native' => 'English',
            'date_format' => 'Y-m-d',
            'datetime_format' => 'Y-m-d H:i',
            'decimal_separator' => '.',
            'thousands_separator' => ',',
        ],
        'de' => [
            'label' => 'German',
            'native' => 'Deutsch',
            'date_format' => 'd.m.Y',
            'datetime_format' => 'd.m.Y H:i',
            'decimal_separator' => ',',
            'thousands_separator' => '.',
        ],
        'fr' => [
            'label' => 'French',
            'native' => 'Français',
            'date_format' => 'd/m/Y',
            'datetime_format' => 'd/m/Y H:i',
            'decimal_separator' => ',',
            'thousands_separator' => ' ',
        ],
        'it' => [
            'label' => 'Italian',
            'native' => 'Italiano',
            'date_format' => 'd/m/Y',
            'datetime_format' => 'd/m/Y H:i',
            'decimal_separator' => ',',
            'thousands_separator' => '.',
        ],
        'es' => [
            'label' => 'Spanish',
            'native' => 'Español',
            'date_format' => 'd/m/Y',
            'datetime_format' => 'd/m/Y H:i',
            'decimal_separator' => ',',
            'thousands_separator' => '.',
        ],
    ],

    'rule_templates' => [
        'chain_wax' => [
            'name_key' => 'ruleTemplate.chainWax',
            'rule_kind' => 'distance',
            'distance_km' => 300,
            'interval_days' => null,
            'email_enabled' => true,
        ],
        'chain_check' => [
            'name_key' => 'ruleTemplate.chainCheck',
            'rule_kind' => 'distance',
            'distance_km' => 500,
            'interval_days' => null,
            'email_enabled' => true,
        ],
        'tubeless_fluid' => [
            'name_key' => 'ruleTemplate.tubelessFluid',
            'rule_kind' => 'time',
            'distance_km' => null,
            'interval_days' => 90,
            'email_enabled' => true,
        ],
        'brake_pads' => [
            'name_key' => 'ruleTemplate.brakePads',
            'rule_kind' => 'distance',
            'distance_km' => 1000,
            'interval_days' => null,
            'email_enabled' => true,
        ],
        'tire_check' => [
            'name_key' => 'ruleTemplate.tireCheck',
            'rule_kind' => 'distance',
            'distance_km' => 1000,
            'interval_days' => null,
            'email_enabled' => true,
        ],
        'general_service' => [
            'name_key' => 'ruleTemplate.generalService',
            'rule_kind' => 'combined',
            'distance_km' => 2000,
            'interval_days' => 180,
            'email_enabled' => true,
        ],
    ],

    'cron_token' => env('RIDEWRENCH_CRON_TOKEN', ''),
];
