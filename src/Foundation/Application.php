<?php

namespace Orchestra\Testbench\Foundation;

use WpStarter\Support\Arr;
use Orchestra\Testbench\Concerns\CreatesApplication;

class Application
{
    use CreatesApplication {
        resolveApplication as protected resolveApplicationFromTrait;
    }

    /**
     * The application base path.
     *
     * @var string
     */
    protected $basePath;

    /**
     * List of configurations.
     *
     * @var array<string, mixed>
     */
    protected $config = [];

    /**
     * The application resolving callback.
     *
     * @var callable(\WpStarter\Foundation\Application):void|null
     */
    protected $resolvingCallback;

    /**
     * Load Environment variables.
     *
     * @var bool
     */
    protected $loadEnvironmentVariables = false;

    /**
     * Create new application resolver.
     *
     * @param  string  $basePath
     * @param  callable(\WpStarter\Foundation\Application):void|null  $resolvingCallback
     */
    public function __construct(string $basePath, ?callable $resolvingCallback = null)
    {
        $this->basePath = $basePath;
        $this->resolvingCallback = $resolvingCallback;
    }

    /**
     * Create new application instance.
     *
     * @param  string  $basePath
     * @param  callable(\WpStarter\Foundation\Application):void|null  $resolvingCallback
     * @param  array  $options
     * @return \WpStarter\Foundation\Application
     */
    public static function create(string $basePath, ?callable $resolvingCallback = null, array $options = [])
    {
        return (new static($basePath, $resolvingCallback))->configure($options)->createApplication();
    }

    /**
     * Configure the application options.
     *
     * @param  array<string, mixed>  $options
     * @return $this
     */
    public function configure(array $options)
    {
        if (isset($options['load_environment_variables']) && \is_bool($options['load_environment_variables'])) {
            $this->loadEnvironmentVariables = $options['load_environment_variables'];
        }

        if (isset($options['enables_package_discoveries']) && \is_bool($options['enables_package_discoveries'])) {
            Arr::set($options, 'extra.dont-discover', []);
        }

        $this->config = Arr::only($options['extra'] ?? [], ['dont-discover', 'providers']);

        return $this;
    }

    /**
     * Ignore package discovery from.
     *
     * @return array
     */
    public function ignorePackageDiscoveriesFrom()
    {
        return $this->config['dont-discover'] ?? [];
    }

    /**
     * Get package providers.
     *
     * @param  \WpStarter\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return $this->config['providers'] ?? [];
    }

    /**
     * Resolve application implementation.
     *
     * @return \WpStarter\Foundation\Application
     */
    protected function resolveApplication()
    {
        return ws_tap($this->resolveApplicationFromTrait(), function ($app) {
            if (\is_callable($this->resolvingCallback)) {
                \call_user_func($this->resolvingCallback, $app);
            }
        });
    }

    /**
     * Get base path.
     *
     * @return string
     */
    protected function getBasePath()
    {
        return $this->basePath;
    }
}
