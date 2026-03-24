<?php

return [
    'default' => 'default',
    'documentations' => [
        'default' => [
            'api' => [
                'title' => env('APP_NAME', 'Kresekin Auth API'),
            ],
            'routes' => [
                'api' => 'api/documentation',
            ],
            'paths' => [
                'use_absolute_path' => env('L5_SWAGGER_USE_ABSOLUTE_PATH', false),
                'swagger_ui_assets_path' => 'vendor/swagger-api/swagger-ui/dist/',
                'docs_json' => 'api-docs.json',
                'docs_yaml' => 'api-docs.yaml',
                'format_to_use_for_docs' => 'json',
                'annotations' => [
                    base_path('app'),
                ],
            ],
        ],
    ],
    'defaults' => [
        'routes' => [
            'docs' => 'docs',
            'oauth2_callback' => 'api/oauth2-callback',
            'middleware' => [
                'api' => [],
                'asset' => [],
                'docs' => [],
                'oauth2_callback' => [],
            ],
            'group_options' => [],
        ],
        'paths' => [
            'docs' => storage_path('api-docs'),
            'views' => base_path('resources/views/vendor/l5-swagger'),
            'base' => env('L5_SWAGGER_BASE_PATH', null),
            'excludes' => [],
        ],
        'scanOptions' => [
            'default_processors_configuration' => [],
            'analyser' => null,
            'analysis' => null,
            'processors' => [],
            'pattern' => null,
            'exclude' => [],
            'open_api_spec_version' => \L5Swagger\Generator::OPEN_API_DEFAULT_SPEC_VERSION,
        ],
        'securityDefinitions' => [
            'securitySchemes' => [
                'bearerAuth' => [
                    'type' => 'http',
                    'description' => 'Enter a JWT bearer token.',
                    'scheme' => 'bearer',
                    'bearerFormat' => 'JWT',
                ],
            ],
            'security' => [],
        ],
        'generate_always' => env('L5_SWAGGER_GENERATE_ALWAYS', false),
        'generate_yaml_copy' => false,
        'proxy' => false,
        'additional_config_url' => null,
        'operations_sort' => 'alpha',
        'validator_url' => null,
        'ui' => [
            'display' => [
                'dark_mode' => false,
                'doc_expansion' => 'list',
                'filter' => true,
            ],
            'authorization' => [
                'persist_authorization' => true,
            ],
        ],
        'constants' => [
            'L5_SWAGGER_CONST_HOST' => env('APP_URL', 'http://localhost:8000'),
        ],
    ],
];
