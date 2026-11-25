<?php

namespace Tests\Feature;

use Illuminate\Support\Env;
use Tests\TestCase;

class CorsConfigurationTest extends TestCase
{
    public function test_preflight_accepts_only_explicit_origin_and_keeps_local_default(): void
    {
        $this->assertContains('http://127.0.0.1:5173', config('cors.allowed_origins'));
        config(['cors.allowed_origins' => ['http://127.0.0.1:5173', 'http://127.0.0.1:5192']]);
        $this->options('/api/client/san-phams', [], [
            'Origin' => 'http://127.0.0.1:5192', 'Access-Control-Request-Method' => 'GET',
            'Access-Control-Request-Headers' => 'authorization,content-type',
        ])->assertSuccessful()->assertHeader('Access-Control-Allow-Origin', 'http://127.0.0.1:5192');
        $this->options('/api/client/san-phams', [], [
            'Origin' => 'http://untrusted.example', 'Access-Control-Request-Method' => 'GET',
        ])->assertHeaderMissing('Access-Control-Allow-Origin');
    }

    public function test_origin_environment_is_parsed_and_wildcards_are_rejected(): void
    {
        $env = Env::getRepository();
        $previous = $env->get('CORS_ALLOWED_ORIGINS');
        try {
            $env->set('CORS_ALLOWED_ORIGINS', 'http://localhost:5173, http://127.0.0.1:5192,http://localhost:5173');
            $config = require base_path('config/cors.php');
            $this->assertSame(['http://localhost:5173', 'http://127.0.0.1:5192'], $config['allowed_origins']);
            $env->set('CORS_ALLOWED_ORIGINS', '*');
            $this->expectException(\InvalidArgumentException::class);
            require base_path('config/cors.php');
        } finally {
            if ($previous === null) $env->clear('CORS_ALLOWED_ORIGINS');
            else $env->set('CORS_ALLOWED_ORIGINS', $previous);
        }
    }
}
