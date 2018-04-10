<?php
namespace Laravay;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class LaravayServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * The commands to be registered.
     *
     * @var array
     */
    protected $commands = [
        'Migration' => 'command.laravay.migration',
        'MakeRole' => 'command.laravay.role',
        'MakePermission' => 'command.laravay.permission',
        'MakeGroup' => 'command.laravay.group',
        'AddLaravayUserTraitUse' => 'command.laravay.add-trait',
        'Setup' => 'command.laravay.setup',
        'SetupGroups' => 'command.laravay.setup-groups',
        'MakeSeeder' => 'command.laravay.seeder',
    ];

    /**
     * The middlewares to be registered.
     *
     * @var array
     */
    protected $middlewares = [
        'role' => \Laravay\Middleware\LaravayRole::class,
        'permission' => \Laravay\Middleware\LaravayPermission::class,
        'ability' => \Laravay\Middleware\LaravayAbility::class,
    ];

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        //
        $this->mergeConfigFrom(__DIR__.'/../config/laravay.php', 'laravay');

        $this->publishes([
            __DIR__.'/../config/laravay.php' => config_path('laravay.php'),
            __DIR__. '/../config/laravay_seeder.php' => config_path('laravay_seeder.php'),
        ], 'laravay');

        $this->useMorphMapForRelationships();

        $this->registerMiddlewares();
    }


    /**
     * If the user wants to use the morphMap it uses the morphMap.
     *
     * @return void
     */
    protected function useMorphMapForRelationships()
    {
        if ($this->app['config']->get('laravay.use_morph_map')) {
            Relation::morphMap($this->app['config']->get('laravay.user_models'));
        }
    }


    /**
     * Register the middlewares automatically.
     *
     * @return void
     */
    protected function registerMiddlewares()
    {
        if (!$this->app['config']->get('laravay.middleware.register')) {
            return;
        }
        $router = $this->app['router'];
        if (method_exists($router, 'middleware')) {
            $registerMethod = 'middleware';
        } elseif (method_exists($router, 'aliasMiddleware')) {
            $registerMethod = 'aliasMiddleware';
        } else {
            return;
        }
        foreach ($this->middlewares as $key => $class) {
            $router->$registerMethod($key, $class);
        }
    }


    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->registerLaravay();
        $this->registerCommands();
    }

    /**
     * Register the application bindings.
     *
     * @return void
     */
    private function registerLaravay()
    {
        $this->app->bind('laravay', function ($app) {
            return new Laravay($app);
        });
        $this->app->alias('laravay', 'Laravay\Laravay');
    }

    /**
     * Register the given commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        foreach (array_keys($this->commands) as $command) {
            $method = "register{$command}Command";
            call_user_func_array([$this, $method], []);
        }
        $this->commands(array_values($this->commands));
    }

    protected function registerMigrationCommand()
    {
        $this->app->singleton('command.laravay.migration', function () {
            return new \Laravay\Commands\MigrationCommand();
        });
    }

    protected function registerMakeRoleCommand()
    {
        $this->app->singleton('command.laravay.role', function ($app) {
            return new \Laravay\Commands\MakeRoleCommand($app['files']);
        });
    }

    protected function registerMakePermissionCommand()
    {
        $this->app->singleton('command.laravay.permission', function ($app) {
            return new \Laravay\Commands\MakePermissionCommand($app['files']);
        });
    }

    protected function registerMakeGroupCommand()
    {
        $this->app->singleton('command.laravay.group', function ($app) {
            return new \Laravay\Commands\MakeGroupCommand($app['files']);
        });
    }

    protected function registerAddLaravayUserTraitUseCommand()
    {
        $this->app->singleton('command.laravay.add-trait', function () {
            return new \Laravay\Commands\AddLaravayUserTraitUseCommand();
        });
    }

    protected function registerSetupCommand()
    {
        $this->app->singleton('command.laravay.setup', function () {
            return new \Laravay\Commands\SetupCommand();
        });
    }

    protected function registerSetupGroupsCommand()
    {
        $this->app->singleton('command.laravay.setup-groups', function () {
            return new \Laravay\Commands\SetupGroupsCommand();
        });
    }

    protected function registerMakeSeederCommand()
    {
        $this->app->singleton('command.laravay.seeder', function () {
            return new \Laravay\Commands\MakeSeederCommand();
        });
    }

    /**
     * Get the services provided.
     *
     * @return array
     */
    public function provides()
    {
        return array_values($this->commands);
    }

}