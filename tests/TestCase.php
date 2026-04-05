<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        if (! $this->app) {
            $this->refreshApplication();
            $this->app['config']->set('database.default', 'sqlite');
            $this->app['config']->set('database.connections.sqlite.database', ':memory:');
            $this->app['config']->set('webpush.database_connection', 'sqlite');
            $this->app['db']->setDefaultConnection('sqlite');
            $this->app['db']->purge('pgsql');
        }

        parent::setUp();
        $this->withoutVite();
    }
}
