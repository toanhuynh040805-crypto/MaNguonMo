<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * RefreshDatabase wipes every table. Real environment variables (Docker,
     * a shell export) take precedence over phpunit.xml, so refuse to run
     * unless the suite really points at the in-memory SQLite database.
     */
    public function createApplication()
    {
        $app = parent::createApplication();

        $connection = $app['config']->get('database.default');
        $database = $app['config']->get("database.connections.{$connection}.database");
        if ($connection !== 'sqlite' || $database !== ':memory:') {
            throw new RuntimeException(
                "Tests must use SQLite :memory:, got [{$connection}:{$database}]. "
                . 'Inside Docker run: docker compose exec app sh docker/run-tests.sh'
            );
        }

        return $app;
    }
}
