<?php

namespace Orchestra\Testbench\Concerns\Database;

use WpStarter\Contracts\Config\Repository;
use WpStarter\Support\Arr;
use WpStarter\Support\Collection;
use WpStarter\Support\Str;

trait HandlesConnections
{
    /**
     * Allow to use database connections environment variables.
     */
    final protected function usesDatabaseConnectionsEnvironmentVariables(Repository $config, string $driver, string $keyword): void
    {
        $keyword = Str::upper($keyword);

        $configurations = [];
        $options = [
            'url' => 'URL',
            'host' => 'HOST',
            'port' => 'PORT',
            'database' => ['DB', 'DATABASE'],
            'username' => ['USER', 'USERNAME'],
            'password' => 'PASSWORD',
        ];

        foreach ($options as $key => $value) {
            $configurations["database.connections.{$driver}.{$key}"] = Collection::make(
                Arr::wrap($value)
            )->transform(static function ($value) use ($keyword) {
                return ws_env("{$keyword}_{$value}");
            })->first(static function ($value) {
                return ! \is_null($value);
            }) ?? $config->get("database.connections.{$driver}.{$key}");
        }

        $config->set($configurations);
    }
}
