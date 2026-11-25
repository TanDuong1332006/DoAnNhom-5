<?php
$origins = array_values(array_unique(array_filter(array_map('trim', explode(',', (string) env(
    'CORS_ALLOWED_ORIGINS',
    'http://localhost:5173,http://127.0.0.1:5173,http://localhost:3000'
))))));
foreach ($origins as $origin) {
    $parts = parse_url($origin);
    if (!$parts || !in_array($parts['scheme'] ?? '', ['http', 'https'], true)
        || empty($parts['host']) || str_contains($origin, '*')
        || isset($parts['user']) || isset($parts['pass'])
        || isset($parts['path']) || isset($parts['query']) || isset($parts['fragment'])) {
        throw new InvalidArgumentException('CORS_ALLOWED_ORIGINS requires explicit HTTP(S) origins without paths or wildcards.');
    }
}
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => $origins,
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
