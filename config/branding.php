<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Institution identity
    |--------------------------------------------------------------------------
    |
    | School name comes from APP_NAME in .env. Portal title and logo are below.
    |
    */
    'portal_title' => env('BRAND_PORTAL_TITLE', 'Zendy Portal'),
    'logo_path' => env('BRAND_LOGO_PATH', 'images/d.png'),

    /*
    |--------------------------------------------------------------------------
    | Optional extra stylesheet (under /public)
    |--------------------------------------------------------------------------
    |
    | Example: BRANDING_CSS=branding/portal.css
    |
    */
    'css_path' => env('BRANDING_CSS'),

    /*
    |--------------------------------------------------------------------------
    | Portal colors (Zendy UI)
    |--------------------------------------------------------------------------
    |
    | Loaded after zendy-app.css and override CSS variables.
    |
    */
    'colors' => [
        'primary' => env('BRAND_PRIMARY', '#2563eb'),
        'primary_hover' => env('BRAND_PRIMARY_HOVER', '#1d4ed8'),
        'sidebar_bg' => env('BRAND_SIDEBAR_BG', '#0f172a'),
        'sidebar_bg_hover' => env('BRAND_SIDEBAR_BG_HOVER', '#1e293b'),
        'sidebar_border' => env('BRAND_SIDEBAR_BORDER', '#334155'),
        'sidebar_text' => env('BRAND_SIDEBAR_TEXT', '#cbd5e1'),
        'sidebar_text_muted' => env('BRAND_SIDEBAR_TEXT_MUTED', '#94a3b8'),
        'body_bg' => env('BRAND_BODY_BG', '#f1f5f9'),
        'surface' => env('BRAND_SURFACE', '#ffffff'),
        'text' => env('BRAND_TEXT', '#0f172a'),
        'text_muted' => env('BRAND_TEXT_MUTED', '#64748b'),
        'border' => env('BRAND_BORDER', '#e2e8f0'),
        'success' => env('BRAND_SUCCESS', '#16a34a'),
        'warning' => env('BRAND_WARNING', '#d97706'),
        'danger' => env('BRAND_DANGER', '#dc2626'),
    ],

];
