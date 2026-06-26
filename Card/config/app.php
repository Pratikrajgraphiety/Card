<?php

return [
    'name' => 'AstitvaHub',
    'tagline' => 'One Link. Your Complete Digital Identity.',
    'env' => getenv('APP_ENV') ?: 'local',
    'debug' => filter_var(getenv('APP_DEBUG') ?: true, FILTER_VALIDATE_BOOLEAN),
    'key' => getenv('APP_KEY') ?: 'change-this-astitvahub-key-in-production',
    'url' => rtrim((string) (getenv('APP_URL') ?: ''), '/'),
    'timezone' => getenv('APP_TIMEZONE') ?: 'Asia/Kolkata',
    'session_name' => 'astitvahub_session',
    'remember_cookie' => 'astitvahub_remember',
    'contact' => [
        'phones' => ['9619448959', '9619448955', '9076461179'],
        'email' => 'GRAPHIETYOFFICIALL@GMAIL.COM',
    ],
    'upload_max_mb' => 8,
    'allowed_image_mimes' => ['image/jpeg', 'image/png', 'image/webp'],
    'allowed_document_mimes' => [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ],
    'themes' => [
        'aurora' => ['name' => 'Aurora', 'accent' => '#7c3aed', 'secondary' => '#06b6d4'],
        'graphite' => ['name' => 'Graphite', 'accent' => '#64748b', 'secondary' => '#14b8a6'],
        'emerald' => ['name' => 'Emerald', 'accent' => '#10b981', 'secondary' => '#38bdf8'],
        'rose' => ['name' => 'Rose', 'accent' => '#f43f5e', 'secondary' => '#f59e0b'],
        'indigo' => ['name' => 'Indigo', 'accent' => '#6366f1', 'secondary' => '#22d3ee'],
    ],
];
