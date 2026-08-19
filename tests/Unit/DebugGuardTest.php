<?php

namespace Tests\Unit;

use App\Providers\AppServiceProvider;
use PHPUnit\Framework\TestCase;

/**
 * WP-01: debug error pages must render only in genuine local development
 * (APP_ENV=local over plain HTTP). Every hosted context forces debug off,
 * whatever the box .env says. This is the truth table of that decision.
 */
class DebugGuardTest extends TestCase
{
    public function test_debug_allowed_only_for_local_env_over_plain_http(): void
    {
        // local dev box: APP_ENV=local, no forced https -> debug stays on
        $this->assertTrue(AppServiceProvider::debugAllowed(isLocalEnv: true, forceHttps: false));

        // hosted (dev/staging/prod): debug is never allowed
        $this->assertFalse(AppServiceProvider::debugAllowed(isLocalEnv: true, forceHttps: true), 'local env but TLS host -> off');
        $this->assertFalse(AppServiceProvider::debugAllowed(isLocalEnv: false, forceHttps: false), 'non-local env -> off');
        $this->assertFalse(AppServiceProvider::debugAllowed(isLocalEnv: false, forceHttps: true), 'non-local + TLS -> off');
    }
}
