<?php
return [
    'kernel' => 'AppKernel',

    'paths' =>[
        'stub' => dirname(__DIR__).'/../Commands/stubs',
    ],

    'service' =>[
        'name' => 'Service',
        'namespace' => 'Services',
    ],

    'bundles' =>[
        'generator' => [

            'paths' => [
                'module' => 'Modules',
                'service' => 'Services'
            ],

            'files' =>[
                'bundle' => '%BUNDLE_NAME%.php',
                'service' => '%SERVICE_NAMESPACE%/%SERVICE_NAME%.php',
                'bundle_composer' => 'composer.json',
            ]
        ],

        'replacements' =>[
            'kernel' => ['KERNEL_NAME'],
            'bundle' => ['BUNDLE_NAME', 'SERVICE_NAMESPACE','SERVICE_NAME', 'BUNDLE_STUDLY_NAME' , 'BUNDLE_NAMESPACE'],
            'service' => ['SERVICE_NAME', 'SERVICE_NAMESPACE', 'BUNDLE_NAME', 'BUNDLE_NAMESPACE'],
            'bundle_composer' => ['BUNDLE_VENDOR', 'BUNDLE_LOWER_NAME' , 'BUNDLE_NAMESPACE_STR', 'BUNDLE_AUTHOR_NAME', 'BUNDLE_AUTHOR_EMAIL'],
            'service_class' => ['SERVICE_CLASS_NAMESPACE', 'SERVICE_CLASS_NAME'],
            'service_config' => ['SERVICE_LOWER_NAME'],
            'service_composer' => ['BUNDLE_LOWER_NAME', 'SERVICE_VENDOR', 'SERVICE_LOWER_NAME', 'SERVICE_AUTHOR_NAME', 'SERVICE_AUTHOR_EMAIL', 'SERVICE_NAMESPACE_STR'],
        ],
    ],

    'modules' => [
        'generator' => [

            'paths' => [
                'assets' => 'Assets',
                'config' => 'Config',
                'command' => 'Console',
                'model' => 'Entities',
                'event' => 'Events',
                'exception' => 'Exceptions',
                'api_controller' => 'HttpApi/Controllers',
                'api_middleware' => 'HttpApi/Middleware',
                'api_request' => 'HttpApi/Requests',
                'view_controller' => 'HttpView/Controllers',
                'view_middleware' => 'HttpView/Middleware',
                'view_request' => 'HttpView/Requests',
                'job' => 'Jobs',
                'listener' => 'Listeners',
                'middleware' => 'Middleware',
                //'policies' => 'Policies',
                'provider' => 'Providers',
                'repository' => 'Repositories',
                'lang' => 'Resources/lang',
                'api_views' => 'Resources/views/api',
                'view_views' => 'Resources/views/view',
                'view_views_layouts' => 'Resources/views/view/layouts',
                'migration' => 'Database/Migrations',
                'seeder' => 'Database/Seeders',
            ],

            'files' =>[
                'api_route' => 'HttpApi/routes.php',
                'view_route' => 'HttpView/routes.php',
                'module' => '%MODULE_NAME%.php',
                'module_composer' => 'composer.json',
                'middleware_encrypt_cookies' => 'Middleware/EncryptCookies.php',
                'middleware_verify_csrf_token' => 'Middleware/VerifyCsrfToken.php',
                'module_provider' => 'Providers/%MODULE_STUDLY_NAME%ServiceProvider.php',
                'config' => 'Config/%MODULE_LOWER_NAME%.php',
                'job' => 'Jobs/Job.php',
                'seeder' => 'Database/Seeders/%MODULE_STUDLY_NAME%DatabaseSeeder.php'
            ],

            'default' => [
                'default_controller' => '%VIEW_CONTROLLER_PATH%/IndexController.php',
                'default_route' => 'HttpView/routes.php',
                'default_view' => 'Resources/views/view/index.blade.php',
            ],
        ],

        'replacements' =>[
            'module' => ['MODULE_NAMESPACE', 'BUNDLE_STUDLY_NAME', 'BUNDLE_NAMESPACE_STR','BUNDLE_LOWER_NAME', 'MODULE_STUDLY_NAME' , 'MODULE_NAME','BUNDLE_LOWER_NAME','MODULE_LOWER_NAME','BUNDLE_LOWER_NOT_NAME','MODULE_LOWER_NOT_NAME','MODULE_NAMESPACE_STR', 'API_ROUTE_FILE', 'VIEW_ROUTE_FILE', 'API_CONTROLLER_NAMESPACE', 'VIEW_CONTROLLER_NAMESPACE'],
            'module_composer' => ['MODULE_LOWER_NAME', 'MODULE_NAMESPACE_STR', 'MODULE_AUTHOR_NAME', 'MODULE_AUTHOR_EMAIL','BUNDLE_LOWER_NAME'],
            'middleware_encrypt_cookies' => ['MIDDLEWARE_NAMESPACE'],
            'middleware_verify_csrf_token' => ['MIDDLEWARE_NAMESPACE'],
            'api_route' =>['BUNDLE_LOWER_NOT_NAME','MODULE_LOWER_NOT_NAME','BUNDLE_LOWER_NAME','MODULE_LOWER_NAME'],
            'view_route' =>['BUNDLE_LOWER_NOT_NAME','MODULE_LOWER_NOT_NAME','BUNDLE_LOWER_NAME','MODULE_LOWER_NAME'],
            'module_provider' =>['MODULE_STUDLY_NAME','PROVIDER_NAMESPACE','MODULE_LOWER_NAME','BUNDLE_LOWER_NAME',],
            'config' =>['MODULE_STUDLY_NAME','MODULE_LOWER_NAME'],
            'job' =>['JOB_NAMESPACE','MODULE_STUDLY_NAME','MODULE_LOWER_NAME'],
            'seeder' =>['MODULE_STUDLY_NAME', 'SEED_NAME', 'SEED_NAMESPACE'],

            'default_controller' => ['VIEW_CONTROLLER_PATH', 'MODULE_NAMESPACE', 'VIEW_CONTROLLER_NAMESPACE','BUNDLE_LOWER_NAME','MODULE_LOWER_NAME'],
            'default_route' => ['MODULE_STUDLY_NAME','BUNDLE_LOWER_NOT_NAME','MODULE_LOWER_NOT_NAME','BUNDLE_LOWER_NAME','MODULE_LOWER_NAME'],
            'default_view' => ['BUNDLE_STUDLY_NAME','MODULE_STUDLY_NAME'],

            'controller' => ['CONTROLLER_NAMESPACE', 'CONTROLLER_NAME'],
            'command' => ['COMMAND_NAMESPACE', 'COMMAND_NAME', 'COMMAND_EXECUTE_NAME'],
            'model' => ['MODEL_NAMESPACE', 'MODEL_NAME', 'MODEL_LOWER_NAME', 'MODEL_LOWER_ID'],
            'repository' => ['MODEL_NAMESPACE', 'MODEL_NAME', 'REPOSITORY_NAMESPACE', 'REPOSITORY_NAME'],
            'middleware' => ['MIDDLEWARE_NAMESPACE','MIDDLEWARE_NAME'],
            'provider' => ['PROVIDER_NAMESPACE', 'PROVIDER_NAME'],
            'request' => ['REQUEST_NAMESPACE', 'REQUEST_NAME'],
            'create_job' =>['JOB_NAMESPACE','JOB_NAME'],
            'event' =>['EVENT_NAMESPACE','EVENT_NAME'],
            'listener' =>['LISTENER_NAMESPACE','LISTENER_NAME','EVENT_NAMESPACE','EVENT_NAME'],

        ],
    ],
];