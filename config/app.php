<?php
return [
    'name' => 'Atelier Wood',
    'tagline' => 'Natural Living · Handcrafted for Life',
    'environment' => getenv('APP_ENV') ?: 'staging',
    'currency' => 'INR',
    'version' => '0.9.8.1'
    ,'release_label' => 'Commerce UX · Marketplace Luxe · Responsive Verified · Staging',
    'support_email' => 'studio@example.com',
    'session_idle_seconds' => (int)(getenv('ATELIER_SESSION_IDLE') ?: 1800),
    'session_absolute_seconds' => (int)(getenv('ATELIER_SESSION_ABSOLUTE') ?: 28800),
    'login_window_seconds' => (int)(getenv('ATELIER_LOGIN_WINDOW') ?: 900),
    'login_max_attempts' => (int)(getenv('ATELIER_LOGIN_MAX_ATTEMPTS') ?: 8),
    'customer_login_max_attempts' => (int)(getenv('ATELIER_CUSTOMER_LOGIN_MAX_ATTEMPTS') ?: 8),
    'tax_rate_percent' => (float)(getenv('ATELIER_TAX_RATE') ?: 18),
    'sandbox_webhook_secret' => getenv('ATELIER_SANDBOX_WEBHOOK_SECRET') ?: '',
    'agent_signing_key' => getenv('ATELIER_AGENT_SIGNING_KEY') ?: '',
    'order_prefix' => 'AW',
    'invoice_prefix' => 'AWI',
];
