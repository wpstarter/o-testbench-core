<?php

namespace Orchestra\Testbench\Concerns;

use Closure;
use WpStarter\Database\Events\DatabaseRefreshed;

trait HandlesDatabases
{
    use Database\HandlesConnections;

    /**
     * Setup database requirements.
     *
     * @param  \Closure():void  $callback
     */
    protected function setUpDatabaseRequirements(Closure $callback): void
    {
        ws_tap($this->app['config'], function ($config) {
            $this->usesDatabaseConnectionsEnvironmentVariables($config, 'mysql', 'MYSQL');
            $this->usesDatabaseConnectionsEnvironmentVariables($config, 'pgsql', 'POSTGRES');
            $this->usesDatabaseConnectionsEnvironmentVariables($config, 'sqlsrv', 'MSSQL');
        });

        $this->app['events']->listen(DatabaseRefreshed::class, function () {
            $this->defineDatabaseMigrationsAfterDatabaseRefreshed();
        });

        $this->defineDatabaseMigrations();

        if (method_exists($this, 'parseTestMethodAnnotations')) {
            $this->parseTestMethodAnnotations($this->app, 'define-db');
        }

        $callback();

        $this->defineDatabaseSeeders();

        $this->beforeApplicationDestroyed(function () {
            $this->destroyDatabaseMigrations();
        });
    }

    /**
     * Define database migrations.
     *
     * @return void
     */
    protected function defineDatabaseMigrations()
    {
        // Define database migrations.
    }

    /**
     * Define database migrations after database refreshed.
     *
     * @return void
     */
    protected function defineDatabaseMigrationsAfterDatabaseRefreshed()
    {
        // Define database migrations after database refreshed.
    }

    /**
     * Destroy database migrations.
     *
     * @return void
     */
    protected function destroyDatabaseMigrations()
    {
        // Destroy database migrations.
    }

    /**
     * Define database seeders.
     *
     * @return void
     */
    protected function defineDatabaseSeeders()
    {
        // Define database seeders.
    }
}
