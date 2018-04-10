<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Use MorphMap in relationships between models
    |--------------------------------------------------------------------------
    |
    | If true, the morphMap feature is going to be used. The array values that
    | are going to be used are the ones inside the 'user_models' array.
    |
    */
    'use_morph_map' => false,
    /*
    |--------------------------------------------------------------------------
    | Use cache in the package
    |--------------------------------------------------------------------------
    |
    | Defines if Laravay will use Laravel's Cache to cache the roles and permissions.
    |
    */
    'use_cache' => true,

    /*
    |--------------------------------------------------------------------------
    | Laratrust User Models
    |--------------------------------------------------------------------------
    |
    | This is the array that contains the information of the user models.
    | This information is used in the add-trait command, and for the roles and
    | permissions relationships with the possible user models.
    |
    | The key in the array is the name of the relationship inside the roles and permissions.
    |
    */
    'user_models' => [
        'users' => 'App\User',
    ],

    /*
    |--------------------------------------------------------------------------
    | Laravay Middleware
    |--------------------------------------------------------------------------
    |
    | This configuration helps to customize the Laratrust middleware behavior.
    |
    */
    'middleware' => [
        /**
         * Define if the laravay middleware are registered automatically in the service provider
         */
        'register' => true,
        /**
         * Method to be called in the middleware return case.
         * Available: abort|redirect
         */
        'handling' => 'abort',
        /**
         * Parameter passed to the middleware_handling method
         */
        'params' => '403',
    ],
    /*
    |--------------------------------------------------------------------------
    | Laravay Magic 'can' Method
    |--------------------------------------------------------------------------
    |
    | Supported cases for the magic can method (Refer to the docs).
    | Available: camel_case|snake_case|kebab_case
    |
    */
    'magic_can_method_case' => 'kebab_case',
];