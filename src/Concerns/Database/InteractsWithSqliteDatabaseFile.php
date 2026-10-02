<?php

namespace Orchestra\Testbench\Concerns\Database;

use WpStarter\Filesystem\Filesystem;
use WpStarter\Support\Collection;
use Orchestra\Testbench\Concerns\InteractsWithPublishedFiles;
use PHPUnit\Framework\Attributes\AfterClass;

trait InteractsWithSqliteDatabaseFile
{
    use InteractsWithPublishedFiles;

    /**
     * List of generated files.
     *
     * @var array<int, string>
     */
    protected $files = [];

    /**
     * Drop Sqlite Database.
     *
     * @api
     *
     * @param  callable():void  $callback
     * @return void
     */
    protected function withoutSqliteDatabase(callable $callback): void
    {
        $time = time();
        $filesystem = new Filesystem;

        $database = ws_database_path('database.sqlite');

        if ($filesystem->exists($database)) {
            $filesystem->move($database, $temporary = "{$database}.backup-{$time}");

            $this->files[] = $temporary;
        }

        ws_value($callback);

        if (isset($temporary)) {
            $filesystem->move($temporary, $database);
        }
    }

    /**
     * Drop and create a new Sqlite Database.
     *
     * @api
     *
     * @param  callable():void  $callback
     * @return void
     */
    protected function withSqliteDatabase(callable $callback): void
    {
        $this->withoutSqliteDatabase(static function () use ($callback) {
            $filesystem = new Filesystem;

            $database = ws_database_path('database.sqlite');

            if (! $filesystem->exists($database)) {
                $filesystem->copy($example = "{$database}.example", $database);
            }

            ws_value($callback);

            if (isset($example)) {
                $filesystem->delete($database);
            }
        });
    }

    /**
     * Tear down the Dusk test case class.
     *
     * @return void
     *
     * @codeCoverageIgnore
     */
    #[AfterClass]
    public static function cleanupBackupSqliteDatabaseFilesOnFailed()
    {
        $filesystem = new Filesystem;

        $filesystem->delete(
            (new Collection([
                ...$filesystem->glob(ws_database_path('database.sqlite.backup-*')),
                ...$filesystem->glob(ws_database_path('database.sqlite-*')),
            ]))->filter(static fn ($file) => $filesystem->exists($file))
                ->all()
        );
    }
}
