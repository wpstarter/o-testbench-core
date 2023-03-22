<?php

namespace Orchestra\Testbench\Concerns\Database;

use Closure;
use WpStarter\Database\Connection;
use WpStarter\Database\Schema\Blueprint;
use WpStarter\Database\Schema\SQLiteBuilder;
use WpStarter\Database\SQLiteConnection;
use WpStarter\Support\Fluent;

trait WithSqlite
{
    /**
     * Add support for SQLite drop foreign.
     *
     * @return void
     */
    protected function hotfixForSqliteSchemaBuilder(): void
    {
        Connection::resolverFor('sqlite', static function ($connection, $database, $prefix, $config) {
            return new class($connection, $database, $prefix, $config) extends SQLiteConnection
            {
                public function getSchemaBuilder()
                {
                    if ($this->schemaGrammar === null) {
                        $this->useDefaultSchemaGrammar();
                    }

                    return new class($this) extends SQLiteBuilder
                    {
                        protected function createBlueprint($table, Closure $callback = null)
                        {
                            return new class($table, $callback) extends Blueprint
                            {
                                public function dropForeign($index)
                                {
                                    return new Fluent();
                                }
                            };
                        }
                    };
                }
            };
        });
    }
}
