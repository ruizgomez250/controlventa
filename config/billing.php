<?php

return [
    'currency' => env('BILLING_CURRENCY', 'PYG'),

    'webhook_secret' => env('BILLING_WEBHOOK_SECRET'),

    'activation_ttl_hours' => (int) env('BILLING_ACTIVATION_TTL_HOURS', 72),

    'subscription_days' => (int) env('BILLING_SUBSCRIPTION_DAYS', 30),

    'provider' => env('BILLING_PROVIDER'),

    'checkout_url' => env('BILLING_CHECKOUT_URL'),

    'plan_amount' => (int) env('BILLING_PLAN_AMOUNT', 0),
];
