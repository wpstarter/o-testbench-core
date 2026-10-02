<?php

namespace Orchestra\Testbench\Foundation;

use WpStarter\Console\Application as Artisan;
use WpStarter\Console\Scheduling\ScheduleListCommand;
use WpStarter\Console\Signals;
use WpStarter\Database\Eloquent\Factories\Factory;
use WpStarter\Database\Eloquent\Model;
use WpStarter\Database\Migrations\Migrator;
use WpStarter\Database\Schema\Builder as SchemaBuilder;
use WpStarter\Foundation\Bootstrap\HandleExceptions;
use WpStarter\Foundation\Bootstrap\LoadEnvironmentVariables;
use WpStarter\Foundation\Bootstrap\RegisterProviders;
use WpStarter\Foundation\Console\AboutCommand;
use WpStarter\Foundation\Console\ChannelListCommand;
use WpStarter\Foundation\Console\RouteListCommand;
use WpStarter\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use WpStarter\Foundation\Http\Middleware\PreventRequestsDuringMaintenance;
use WpStarter\Foundation\Http\Middleware\TrimStrings;
use WpStarter\Foundation\Http\Middleware\ValidateCsrfToken;
use WpStarter\Http\Middleware\TrustHosts;
use WpStarter\Http\Middleware\TrustProxies;
use WpStarter\Http\Resources\Json\JsonResource;
use WpStarter\Http\Resources\JsonApi\JsonApiResource;
use WpStarter\Mail\Markdown;
use WpStarter\Queue\Console\WorkCommand;
use WpStarter\Queue\Queue;
use WpStarter\Routing\Middleware\ThrottleRequests;
use WpStarter\Support\Arr;
use WpStarter\Support\EncodedHtmlString;
use WpStarter\Support\Once;
use WpStarter\Support\Sleep;
use WpStarter\Support\Str;
use WpStarter\Validation\Validator;
use WpStarter\View\Component;
use Orchestra\Testbench\Concerns\CreatesApplication;
use Orchestra\Testbench\Console\Commander;
use Orchestra\Testbench\Contracts\Config as ConfigContract;
use Orchestra\Testbench\Workbench\Workbench;

use function Orchestra\Sidekick\Filesystem\join_paths;

/**
 * @api
 *
 * @phpstan-import-type TExtraConfig from \Orchestra\Testbench\Foundation\Config
 * @phpstan-import-type TOptionalExtraConfig from \Orchestra\Testbench\Foundation\Config
 *
 * @phpstan-type TConfig array{
 *   extra?: TOptionalExtraConfig,
 *   load_environment_variables?: bool,
 *   enabled_package_discoveries?: bool
 * }
 */
class Application
{
    use CreatesApplication {
        resolveApplicationResolvingCallback as protected resolveApplicationResolvingCallbackFromTrait;
        resolveApplicationConfiguration as protected resolveApplicationConfigurationFromTrait;
    }

    /**
     * The WpStarter application instance.
     *
     * @var \WpStarter\Foundation\Application|null
     */
    protected $app;

    /**
     * List of configurations.
     *
     * @var array<string, mixed>
     *
     * @phpstan-var TExtraConfig
     */
    protected array $config = [
        'env' => [],
        'providers' => [],
        'dont-discover' => [],
        'bootstrappers' => [],
    ];

    /**
     * The application resolving callback.
     *
     * @var (callable(\WpStarter\Foundation\Application):(void))|null
     */
    protected $resolvingCallback;

    /**
     * Load Environment variables.
     *
     * @var bool
     */
    protected bool $loadEnvironmentVariables = false;

    /**
     * Create new application resolver.
     *
     * @param  string|null  $basePath
     * @param  (callable(\WpStarter\Foundation\Application):(void))|null  $resolvingCallback
     */
    public function __construct(
        protected readonly ?string $basePath = null,
        ?callable $resolvingCallback = null
    ) {
        $this->resolvingCallback = $resolvingCallback;
    }

    /**
     * Create new application resolver.
     *
     * @param  string|null  $basePath
     * @param  (callable(\WpStarter\Foundation\Application):(void))|null  $resolvingCallback
     * @param  array<string, mixed>  $options
     *
     * @phpstan-param TConfig  $options
     *
     * @return static
     */
    public static function make(?string $basePath = null, ?callable $resolvingCallback = null, array $options = [])
    {
        return (new static($basePath, $resolvingCallback))->configure($options);
    }

    /**
     * Create new application resolver from configuration file.
     *
     * @param  \Orchestra\Testbench\Contracts\Config  $config
     * @param  (callable(\WpStarter\Foundation\Application):(void))|null  $resolvingCallback
     * @param  array<string, mixed>  $options
     *
     * @phpstan-param TConfig  $options
     *
     * @return static
     */
    public static function makeFromConfig(ConfigContract $config, ?callable $resolvingCallback = null, array $options = [])
    {
        $basePath = $config['laravel'] ?? static::applicationBasePath();

        return (new static($config['laravel'], $resolvingCallback))->configure(array_merge($options, [
            'load_environment_variables' => is_file("{$basePath}/.env"),
            'extra' => $config->getExtraAttributes(),
        ]));
    }

    /**
     * Create symlink to vendor path via new application instance.
     *
     * @param  string|null  $basePath
     * @param  string  $workingVendorPath
     * @return \WpStarter\Foundation\Application
     *
     * @codeCoverageIgnore
     */
    public static function createVendorSymlink(?string $basePath, string $workingVendorPath)
    {
        $app = static::create(basePath: $basePath, options: ['extra' => ['dont-discover' => ['*']]]);

        (new Actions\CreateVendorSymlink($workingVendorPath))->handle($app);

        return $app;
    }

    /**
     * Delete symlink to vendor path via new application instance.
     *
     * @param  string|null  $basePath
     * @return \WpStarter\Foundation\Application
     *
     * @codeCoverageIgnore
     */
    public static function deleteVendorSymlink(?string $basePath)
    {
        $app = static::create(basePath: $basePath, options: ['extra' => ['dont-discover' => ['*']]]);

        (new Actions\DeleteVendorSymlink)->handle($app);

        return $app;
    }

    /**
     * Create new application instance.
     *
     * @param  string|null  $basePath
     * @param  (callable(\WpStarter\Foundation\Application):(void))|null  $resolvingCallback
     * @param  array<string, mixed>  $options
     *
     * @phpstan-param TConfig  $options
     *
     * @return \WpStarter\Foundation\Application
     */
    public static function create(?string $basePath = null, ?callable $resolvingCallback = null, array $options = [])
    {
        return static::make($basePath, $resolvingCallback, $options)->createApplication();
    }

    /**
     * Create new application instance from configuration file.
     *
     * @param  \Orchestra\Testbench\Contracts\Config  $config
     * @param  (callable(\WpStarter\Foundation\Application):(void))|null  $resolvingCallback
     * @param  array<string, mixed>  $options
     *
     * @phpstan-param TConfig  $options
     *
     * @return \WpStarter\Foundation\Application
     */
    public static function createFromConfig(ConfigContract $config, ?callable $resolvingCallback = null, array $options = [])
    {
        return static::makeFromConfig($config, $resolvingCallback, $options)->createApplication();
    }

    /**
     * Flush the application states.
     *
     * @param  \Orchestra\Testbench\Console\Commander|\Orchestra\Testbench\PHPUnit\TestCase  $instance
     * @return void
     */
    public static function flushState(object $instance): void
    {
        AboutCommand::flushState();
        Artisan::forgetBootstrappers();
        ChannelListCommand::resolveTerminalWidthUsing(null);
        Component::flushCache();
        Component::forgetComponentsResolver();
        Component::forgetFactory();
        ConvertEmptyStringsToNull::flushState();
        EncodedHtmlString::flushState();
        Factory::flushState();

        if (! $instance instanceof Commander) {
            HandleExceptions::flushState($instance);
        }

        JsonResource::flushState();
        JsonApiResource::flushState();
        Markdown::flushState();
        Migrator::withoutMigrations([]);
        Model::handleDiscardedAttributeViolationUsing(null);
        Model::handleLazyLoadingViolationUsing(null);
        Model::handleMissingAttributeViolationUsing(null);
        Model::automaticallyEagerLoadRelationships(false);
        Model::preventAccessingMissingAttributes(false);
        Model::preventLazyLoading(false);
        Model::preventSilentlyDiscardingAttributes(false);
        Once::flush();
        PreventRequestsDuringMaintenance::flushState();
        Queue::createPayloadUsing(null);
        RegisterProviders::flushState();
        RouteListCommand::resolveTerminalWidthUsing(null);
        ScheduleListCommand::resolveTerminalWidthUsing(null);
        SchemaBuilder::$defaultStringLength = 255;
        SchemaBuilder::$defaultMorphKeyType = 'int';
        Signals::resolveAvailabilityUsing(null); // @phpstan-ignore argument.type
        Sleep::fake(false);
        Str::createRandomStringsNormally();
        Str::createUlidsNormally();
        Str::createUuidsNormally();
        ThrottleRequests::shouldHashKeys();
        TrimStrings::flushState();
        TrustProxies::flushState();
        TrustHosts::flushState();
        Validator::flushState();
        ValidateCsrfToken::flushState();
        WorkCommand::flushState();
    }

    /**
     * Configure the application options.
     *
     * @param  array<string, mixed>  $options
     *
     * @phpstan-param TConfig  $options
     *
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

        /** @var TExtraConfig $config */
        $config = Arr::only($options['extra'] ?? [], array_keys($this->config));

        $this->config = $config;

        return $this;
    }

    /**
     * Ignore package discovery from.
     *
     * @api
     *
     * @return array<int, string>
     */
    public function ignorePackageDiscoveriesFrom()
    {
        return $this->config['dont-discover'] ?? [];
    }

    /**
     * Get package providers.
     *
     * @api
     *
     * @param  \WpStarter\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app)
    {
        return $this->config['providers'] ?? [];
    }

    /**
     * Get package bootstrapper.
     *
     * @api
     *
     * @param  \WpStarter\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageBootstrappers($app)
    {
        if (\is_null($bootstrappers = ($this->config['bootstrappers'] ?? null))) {
            return [];
        }

        return Arr::wrap($bootstrappers);
    }

    /**
     * Resolve application resolving callback.
     *
     * @internal
     *
     * @param  \WpStarter\Foundation\Application  $app
     * @return void
     */
    protected function resolveApplicationResolvingCallback($app): void
    {
        $this->resolveApplicationResolvingCallbackFromTrait($app);

        if (\is_callable($this->resolvingCallback)) {
            \call_user_func($this->resolvingCallback, $app);
        }
    }

    /**
     * Resolve the application's base path.
     *
     * @api
     *
     * @return string
     */
    protected function getApplicationBasePath()
    {
        return $this->basePath ?? static::applicationBasePath();
    }

    /**
     * Resolve application core environment variables implementation.
     *
     * @internal
     *
     * @param  \WpStarter\Foundation\Application  $app
     * @return void
     */
    protected function resolveApplicationEnvironmentVariables($app)
    {
        Env::disablePutenv();

        $app->terminating(static function () {
            Env::enablePutenv();
        });

        if ($this->loadEnvironmentVariables === true) {
            $app->make(LoadEnvironmentVariables::class)->bootstrap($app);
        }

        (new Bootstrap\LoadEnvironmentVariablesFromArray($this->config['env'] ?? []))->bootstrap($app);
    }

    /**
     * Resolve application core configuration implementation.
     *
     * @internal
     *
     * @param  \WpStarter\Foundation\Application  $app
     * @return void
     */
    protected function resolveApplicationConfiguration($app)
    {
        $this->resolveApplicationConfigurationFromTrait($app);

        (new Bootstrap\EnsuresDefaultConfiguration)->bootstrap($app);
    }

    /**
     * Resolve application Console Kernel implementation.
     *
     * @api
     *
     * @param  \WpStarter\Foundation\Application  $app
     * @return void
     */
    protected function resolveApplicationConsoleKernel($app)
    {
        if ($this->hasCustomApplicationKernels() === true) {
            return;
        }

        $kernel = Workbench::applicationConsoleKernel() ?? 'Orchestra\Testbench\Console\Kernel';

        if (is_file($app->basePath(join_paths('app', 'Console', 'Kernel.php'))) && class_exists('App\Console\Kernel')) {
            $kernel = 'App\Console\Kernel';
        }

        $app->singleton('WpStarter\Contracts\Console\Kernel', $kernel);
    }

    /**
     * Resolve application HTTP Kernel implementation.
     *
     * @api
     *
     * @param  \WpStarter\Foundation\Application  $app
     * @return void
     */
    protected function resolveApplicationHttpKernel($app)
    {
        if ($this->hasCustomApplicationKernels() === true) {
            return;
        }

        $kernel = Workbench::applicationHttpKernel() ?? 'Orchestra\Testbench\Http\Kernel';

        if (is_file($app->basePath(join_paths('app', 'Http', 'Kernel.php'))) && class_exists('App\Http\Kernel')) {
            $kernel = 'App\Http\Kernel';
        }

        $app->singleton('WpStarter\Contracts\Http\Kernel', $kernel);
    }
}
