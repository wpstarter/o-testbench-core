<?php

namespace Orchestra\Testbench\Concerns;

use WpStarter\Foundation\Application;
use WpStarter\Support\Collection;
use WpStarter\Support\Facades\Facade;
use Orchestra\Testbench\Foundation\PackageManifest;

/**
 * @property bool|null $enablesPackageDiscoveries
 * @property bool|null $loadEnvironmentVariables
 */
trait CreatesApplication
{
    /**
     * Get Application's base path.
     *
     * @return string
     */
    public static function applicationBasePath()
    {
        return $_ENV['APP_BASE_PATH'] ?? realpath(__DIR__.'/../../laravel');
    }

    /**
     * Ignore package discovery from.
     *
     * @return array<int, string>
     */
    public function ignorePackageDiscoveriesFrom()
    {
        if (property_exists($this, 'enablesPackageDiscoveries') && $this->enablesPackageDiscoveries === true) {
            return [];
        }

        return ['*'];
    }

    /**
     * Get application timezone.
     *
     * @param  \WpStarter\Foundation\Application  $app
     * @return string|null
     */
    protected function getApplicationTimezone($app)
    {
        return $app['config']['app.timezone'];
    }

    /**
     * Override application bindings.
     *
     * @param  \WpStarter\Foundation\Application  $app
     * @return array<string|class-string, string|class-string>
     */
    protected function overrideApplicationBindings($app)
    {
        return [];
    }

    /**
     * Resolve application bindings.
     *
     * @param  \WpStarter\Foundation\Application  $app
     * @return void
     */
    final protected function resolveApplicationBindings($app): void
    {
        foreach ($this->overrideApplicationBindings($app) as $original => $replacement) {
            $app->bind($original, $replacement);
        }
    }

    /**
     * Get application aliases.
     *
     * @param  \WpStarter\Foundation\Application  $app
     * @return array<string, class-string>
     */
    protected function getApplicationAliases($app)
    {
        return $app['config']['app.aliases'];
    }

    /**
     * Override application aliases.
     *
     * @param  \WpStarter\Foundation\Application  $app
     * @return array<string, class-string>
     */
    protected function overrideApplicationAliases($app)
    {
        return [];
    }

    /**
     * Resolve application aliases.
     *
     * @param  \WpStarter\Foundation\Application  $app
     * @return array<string, class-string>
     */
    final protected function resolveApplicationAliases($app): array
    {
        $aliases = new Collection($this->getApplicationAliases($app));
        $overrides = $this->overrideApplicationAliases($app);

        if (! empty($overrides)) {
            $aliases->transform(static function ($alias, $name) use ($overrides) {
                return $overrides[$name] ?? $alias;
            });
        }

        return $aliases->merge($this->getPackageAliases($app))->all();
    }

    /**
     * Get package aliases.
     *
     * @param  \WpStarter\Foundation\Application  $app
     * @return array<string, class-string>
     */
    protected function getPackageAliases($app)
    {
        return [];
    }

    /**
     * Get package bootstrapper.
     *
     * @param  \WpStarter\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageBootstrappers($app)
    {
        return [];
    }

    /**
     * Get application providers.
     *
     * @param  \WpStarter\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getApplicationProviders($app)
    {
        return $app['config']['app.providers'];
    }

    /**
     * Override application aliases.
     *
     * @param  \WpStarter\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function overrideApplicationProviders($app)
    {
        return [];
    }

    /**
     * Resolve application aliases.
     *
     * @param  \WpStarter\Foundation\Application  $app
     * @return array<int, class-string>
     */
    final protected function resolveApplicationProviders($app): array
    {
        $providers = new Collection($this->getApplicationProviders($app));
        $overrides = $this->overrideApplicationProviders($app);

        if (! empty($overrides)) {
            $providers->transform(static function ($provider) use ($overrides) {
                return $overrides[$provider] ?? $provider;
            });
        }

        return $providers->merge($this->getPackageProviders($app))->all();
    }

    /**
     * Get package providers.
     *
     * @param  \WpStarter\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app)
    {
        return [];
    }

    /**
     * Get base path.
     *
     * @return string
     */
    protected function getBasePath()
    {
        return static::applicationBasePath();
    }

    /**
     * Creates the application.
     *
     * Needs to be implemented by subclasses.
     *
     * @return \WpStarter\Foundation\Application
     */
    public function createApplication()
    {
        $app = $this->resolveApplication();

        $this->resolveApplicationBindings($app);
        $this->resolveApplicationExceptionHandler($app);
        $this->resolveApplicationCore($app);
        $this->resolveApplicationConfiguration($app);
        $this->resolveApplicationHttpKernel($app);
        $this->resolveApplicationConsoleKernel($app);
        $this->resolveApplicationBootstrappers($app);

        return $app;
    }

    /**
     * Resolve application implementation.
     *
     * @return \WpStarter\Foundation\Application
     */
    protected function resolveApplication()
    {
        return tap(new Application($this->getBasePath()), function ($app) {
            $app->bind(
                'WpStarter\Foundation\Bootstrap\LoadConfiguration',
                'Orchestra\Testbench\Bootstrap\LoadConfiguration'
            );

            PackageManifest::swap($app, $this);
        });
    }

    /**
     * Resolve application core configuration implementation.
     *
     * @param  \WpStarter\Foundation\Application  $app
     * @return void
     */
    protected function resolveApplicationConfiguration($app)
    {
        if (property_exists($this, 'loadEnvironmentVariables') && $this->loadEnvironmentVariables === true) {
            $app->make('WpStarter\Foundation\Bootstrap\LoadEnvironmentVariables')->bootstrap($app);
        }

        $app->make('WpStarter\Foundation\Bootstrap\LoadConfiguration')->bootstrap($app);

        tap($this->getApplicationTimezone($app), static function ($timezone) {
            ! \is_null($timezone) && date_default_timezone_set($timezone);
        });

        $app['config']['app.aliases'] = $this->resolveApplicationAliases($app);
        $app['config']['app.providers'] = $this->resolveApplicationProviders($app);
    }

    /**
     * Resolve application core implementation.
     *
     * @param  \WpStarter\Foundation\Application  $app
     * @return void
     */
    protected function resolveApplicationCore($app)
    {
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($app);

        $app->detectEnvironment(static function () {
            return 'testing';
        });
    }

    /**
     * Resolve application Console Kernel implementation.
     *
     * @param  \WpStarter\Foundation\Application  $app
     * @return void
     */
    protected function resolveApplicationConsoleKernel($app)
    {
        $app->singleton('WpStarter\Contracts\Console\Kernel', 'Orchestra\Testbench\Console\Kernel');
    }

    /**
     * Resolve application HTTP Kernel implementation.
     *
     * @param  \WpStarter\Foundation\Application  $app
     * @return void
     */
    protected function resolveApplicationHttpKernel($app)
    {
        $app->singleton('WpStarter\Contracts\Http\Kernel', 'Orchestra\Testbench\Http\Kernel');
    }

    /**
     * Resolve application HTTP exception handler.
     *
     * @param  \WpStarter\Foundation\Application  $app
     * @return void
     */
    protected function resolveApplicationExceptionHandler($app)
    {
        $app->singleton('WpStarter\Contracts\Debug\ExceptionHandler', 'Orchestra\Testbench\Exceptions\Handler');
    }

    /**
     * Resolve application bootstrapper.
     *
     * @param  \WpStarter\Foundation\Application  $app
     * @return void
     */
    protected function resolveApplicationBootstrappers($app)
    {
        $app->make('WpStarter\Foundation\Bootstrap\HandleExceptions')->bootstrap($app);
        $app->make('WpStarter\Foundation\Bootstrap\RegisterFacades')->bootstrap($app);
        $app->make('WpStarter\Foundation\Bootstrap\SetRequestForConsole')->bootstrap($app);
        $app->make('WpStarter\Foundation\Bootstrap\RegisterProviders')->bootstrap($app);

        if (class_exists('WpStarter\Database\Eloquent\LegacyFactoryServiceProvider')) {
            $app->register('WpStarter\Database\Eloquent\LegacyFactoryServiceProvider');
        }

        if (method_exists($this, 'parseTestMethodAnnotations')) {
            $this->parseTestMethodAnnotations($app, 'environment-setup');
            $this->parseTestMethodAnnotations($app, 'define-env');
        }

        $this->defineEnvironment($app);
        $this->getEnvironmentSetUp($app);

        $app->make('WpStarter\Foundation\Bootstrap\BootProviders')->bootstrap($app);

        foreach ($this->getPackageBootstrappers($app) as $bootstrap) {
            $app->make($bootstrap)->bootstrap($app);
        }

        $app->make('WpStarter\Contracts\Console\Kernel')->bootstrap();

        $refreshNameLookups = static function ($app) {
            $app['router']->getRoutes()->refreshNameLookups();
        };

        $refreshNameLookups($app);

        $app->resolving('url', static function ($url, $app) use ($refreshNameLookups) {
            $refreshNameLookups($app);
        });
    }

    /**
     * Reset artisan commands for the application.
     *
     * @param  \WpStarter\Foundation\Application  $app
     * @return void
     */
    final protected function resetApplicationArtisanCommands($app)
    {
        $app['WpStarter\Contracts\Console\Kernel']->setArtisan(null);
    }

    /**
     * Define environment setup.
     *
     * @param  \WpStarter\Foundation\Application  $app
     * @return void
     */
    protected function defineEnvironment($app)
    {
        // Define environment.
    }

    /**
     * Define environment setup.
     *
     * @param  \WpStarter\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Define your environment setup.
    }
}
