<?php

return [
    'api_key' => env('ASAAS_API_KEY'),
    'environment' => env('ASAAS_ENVIRONMENT', 'sandbox'),
    'webhook_token' => env('ASAAS_WEBHOOK_TOKEN'),
    'master_wallet_id' => env('ASAAS_MASTER_WALLET_ID'),
    'default_income_value' => (float) env('ASAAS_DEFAULT_INCOME_VALUE', 5000),
    'default_birth_date' => env('ASAAS_DEFAULT_BIRTH_DATE', '1990-01-01'),
    'base_urls' => [
        'sandbox' => 'https://api-sandbox.asaas.com',
        'production' => 'https://api.asaas.com',
    ],
];
