<?php

use Illuminate\Support\Facades\{Cache, DB, Event};

function securityBrowserBoot(array $session)
{
    require_once __DIR__.'/../../vendor/autoload.php';
    if (($session['purpose'] ?? '') !== 'SOFEP N5-147 browser UAT'
        || !preg_match('/^sofep_security_uat_[0-9]{8}_[a-f0-9]{12}$/D', $session['database'] ?? '')
        || !is_dir($session['root'] ?? '') || is_file(__DIR__.'/../../bootstrap/cache/config.php')) {
        throw new RuntimeException('Invalid browser UAT session.');
    }
    $app = require __DIR__.'/../../bootstrap/app.php';
    $app->afterBootstrapping(Illuminate\Foundation\Bootstrap\LoadConfiguration::class, function ($app) use ($session) {
        $mysql = config('database.connections.mysql');
        if (!in_array($mysql['host'], ['localhost','127.0.0.1','::1'], true) || !empty($mysql['url']) || $mysql['database'] === $session['database']) {
            throw new RuntimeException('Unsafe source connection.');
        }
        $mysql['database'] = $session['database']; $mysql['url'] = null;
        config([
            'database.connections' => ['security_browser' => $mysql], 'database.default' => 'security_browser',
            'app.env' => 'testing', 'app.debug' => false,
            'cache.default' => 'database', 'cache.prefix' => $session['cache_prefix'],
            'cache.stores.database.connection' => 'security_browser', 'cache.stores.database.lock_connection' => 'security_browser',
            'cache.stores.database.table' => 'cache', 'cache.stores.database.lock_table' => 'cache_locks',
            'session.driver' => 'array', 'queue.default' => 'sync', 'logging.default' => 'null',
            'mail.default' => 'array', 'mail.mailers' => ['array' => ['transport' => 'array']],
            'mail.from.address' => 'no-reply@sofep.example.test', 'mail.from.name' => 'SOFEP UAT',
            'cors.allowed_origins' => ['http://127.0.0.1:5187'],
            'filesystems.disks.local.root' => $session['root'].'/private',
            'filesystems.disks.public.root' => $session['root'].'/public',
        ]);
        $app->instance('env', 'testing');
    });
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    $limiter = app(Illuminate\Cache\RateLimiter::class);
    $property = new ReflectionProperty($limiter, 'cache');
    $store = $property->getValue($limiter)->getStore();
    $default = Cache::store()->getStore();
    foreach ([$store, $default] as $candidate) {
        if (!$candidate instanceof Illuminate\Cache\DatabaseStore
            || $candidate->getConnection()->getDatabaseName() !== $session['database']
            || $candidate->getLockConnection()->getDatabaseName() !== $session['database']
            || $candidate->getPrefix() !== $session['cache_prefix']) throw new RuntimeException('Cache escaped UAT.');
    }
    if (DB::selectOne('SELECT DATABASE() AS name')->name !== $session['database']) throw new RuntimeException('Wrong UAT database.');
    Event::listen(Illuminate\Mail\Events\MessageSending::class, function ($event) {
        foreach (array_merge($event->message->getTo(), $event->message->getCc(), $event->message->getBcc()) as $address) {
            if (!preg_match('/^[a-z0-9.+_-]+@sofep\.example\.test$/iD', $address->getAddress())) throw new RuntimeException('UAT mailbox allowlist blocked destination.');
        }
    });
    Event::listen(Illuminate\Mail\Events\MessageSent::class, function ($event) use ($session) {
        $email = $event->sent->getOriginalMessage();
        $id = bin2hex(random_bytes(6));
        file_put_contents($session['root'].'/mail-'.$id.'.eml', $email->toString());
        file_put_contents($session['root'].'/mailbox.jsonl', json_encode([
            'file' => 'mail-'.$id.'.eml', 'to' => array_map(fn ($a) => $a->getAddress(), $email->getTo()),
            'subject' => $email->getSubject(), 'text' => $email->getTextBody(), 'html' => $email->getHtmlBody(), 'at' => gmdate(DATE_ATOM),
        ], JSON_UNESCAPED_UNICODE).PHP_EOL, FILE_APPEND | LOCK_EX);
    });
    return $app;
}
