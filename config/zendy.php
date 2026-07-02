<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Zendy SSO
    |--------------------------------------------------------------------------
    |
    | Set to true once Zendy provides working JWT SSO credentials.
    | When false, the portal uses direct-link tracking only.
    |
    */
    'sso_enabled' => env('ZENDY_SSO_ENABLED', false),

    'redirect_url' => env('ZENDY_REDIRECT_URL', 'https://zendy.io/'),

    'sso_url' => env('ZENDY_SSO_URL', 'https://zendy.io/sso-login'),
];
