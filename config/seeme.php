<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Driver
    |--------------------------------------------------------------------------
    | 'api' — sends real SMS via the SeeMe HTTP API (default)
    | 'log' — writes to the Laravel log, no HTTP call (local/staging)
    */
    'driver' => env('SEEME_DRIVER', 'api'),

    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    | Your SeeMe SMS Gateway API key. Generate it under Gateway Settings
    | in the SeeMe admin panel.
    */
    'api_key' => env('SEEME_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Default Sender
    |--------------------------------------------------------------------------
    | The sender ID shown on the recipient's phone. Can be overridden
    | per-message via SeeMeMessage::sender().
    */
    'sender' => env('SEEME_SENDER', ''),

    /*
    |--------------------------------------------------------------------------
    | Gateway URL
    |--------------------------------------------------------------------------
    */
    'base_url' => env('SEEME_BASE_URL', 'https://seeme.hu/gateway'),

    /*
    |--------------------------------------------------------------------------
    | Webhooks
    |--------------------------------------------------------------------------
    | SeeMe can call back your application for delivery reports and inbound
    | SMS messages. Enable each webhook and set the path it should be
    | registered on. The allowed_ips value restricts webhook access to
    | SeeMe's callback IP (found in the SeeMe admin under "Callback forrás").
    | Leave null to disable IP validation (not recommended in production).
    */
    'webhooks' => [

        'allowed_ips' => env('SEEME_CALLBACK_IP'),

        'delivery_report' => [
            'enabled' => env('SEEME_DELIVERY_REPORT_ENABLED', false),
            'path'    => env('SEEME_DELIVERY_REPORT_PATH', 'seeme/delivery-report'),
        ],

        'inbound_sms' => [
            'enabled' => env('SEEME_INBOUND_SMS_ENABLED', false),
            'path'    => env('SEEME_INBOUND_SMS_PATH', 'seeme/inbound'),
        ],

    ],

];
