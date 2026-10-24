<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if ($this->app->environment('testing')) {
            $pdo = DB::connection()->getPdo();

            // The admin and user connections are aliases to the same database
            // (see config/database.php). Point them at the default connection's
            // PDO so RefreshDatabase's per-test transaction also covers portal
            // and admin writes; otherwise MySQL keeps separate PDOs that are
            // never rolled back between tests.
            foreach (['admin', 'user'] as $conn) {
                DB::connection($conn)->setPdo($pdo);
            }
        }
    }
}
