<?php

return [
    'gateway' => getenv('PAYMENT_GATEWAY') ?: 'cashfree',
    'cashfree' => [
        'app_id' => getenv('CASHFREE_APP_ID') ?: '',
        'secret_key' => getenv('CASHFREE_SECRET_KEY') ?: '',
        'environment' => getenv('CASHFREE_ENVIRONMENT') ?: 'sandbox',
        'api_version' => getenv('CASHFREE_API_VERSION') ?: '2025-01-01',
        'sdk_url' => 'https://sdk.cashfree.com/js/v3/cashfree.js',
        'sandbox_api_url' => 'https://sandbox.cashfree.com/pg',
        'production_api_url' => 'https://api.cashfree.com/pg',
    ],
];
