<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel CORS Options
    |--------------------------------------------------------------------------
    |
    | The allowed_methods and allowed_headers options are case-insensitive.
    |
    | You don't need to provide both allowed_origins and allowed_origins_patterns.
    | If one of the strings passed matches, it is considered a valid origin.
    |
    | If ['*'] is provided to allowed_methods, allowed_origins or allowed_headers
    | all methods / origins / headers are allowed.
    |
    */

    /*
     * You can enable CORS for 1 or multiple paths.
     * Example: ['api/*']
     */
    'paths' => ['api/*', 'admin/*', 'app/*'],

    /*
    * Matches the request method. `['*']` allows all methods.
    */
    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'PATCH'],

    /*
     * Matches the request origin. `['*']` allows all origins. Wildcards can be used, eg `*.mydomain.com`
     */
    'allowed_origins' => ['*'],

    /*
     * Patterns that can be used with `preg_match` to match the origin.
     * 例如: ['/^https:\/\/.*\.example\.com$/']
     */
    'allowed_origins_patterns' => [],

    /*
     * Sets the Access-Control-Allow-Headers response header. `['*']` allows all headers.
     */
    'allowed_headers' => [
        'Origin',
        'Content-Type',
        'Cookie',
        'X-CSRF-TOKEN',
        'Accept',
        'Authorization',
        'applyid',
        'openid',
        'apply-secret',
        'versionid',
        'X-XSRF-TOKEN',
        'Lang',
        'X-Requested-With',
        'X-HTTP-Method-Override'
    ],

    /*
     * Sets the Access-Control-Expose-Headers response header with these headers.
     */
    'exposed_headers' => ['Authorization', 'authenticated', 'X-Total-Count'],

    /*
     * Sets the Access-Control-Max-Age response header when > 0.
     */
    'max_age' => 86400, // 24小时

    /*
     * Sets the Access-Control-Allow-Credentials header.
     */
    'supports_credentials' => true,
];
