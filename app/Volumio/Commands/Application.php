<?php

namespace App\Volumio\Commands;

use Illuminate\Container\Container;

class Application extends Container
{
    protected $loadedConfigurations = [];
    protected $loadedProviders = [];
    protected $configPath;
    protected $basePath;
    
    public function __construct()
    {
        $this->registerBaseBindings();
        $this->registerDatabaseBindings();
        $this->registerEventBindings();
    }
    
    public function basePath($path = null)
    {
        if (isset($this->basePath)) {
            return $this->basePath.($path ? '/'.$path : $path);
        }

        if ($this->runningInConsole()) {
            $this->basePath = getcwd();
        } else {
            $this->basePath = realpath(getcwd().'/../');
        }

        return $this->basePath($path);
    }
    
    public function runningInConsole()
    {
        return php_sapi_name() == 'cli';
    }
    
    public function getConfigurationPath($name = null)
    {
        if (! $name) {
            $appConfigDir = ($this->configPath ?: $this->basePath('config')).'/';

            if (file_exists($appConfigDir)) {
                return $appConfigDir;
            } elseif (file_exists($path = __DIR__.'/../config/')) {
                return $path;
            }
        } else {
            $appConfigPath = ($this->configPath ?: $this->basePath('config')).'/'.$name.'.php';

            if (file_exists($appConfigPath)) {
                return $appConfigPath;
            } elseif (file_exists($path = __DIR__.'/../config/'.$name.'.php')) {
                return $path;
            }
        }
    }

    protected function registerEventBindings()
    {
        $this->singleton('events', function () {
            $this->register('Illuminate\Events\EventServiceProvider');

            return $this->make('events');
        });
    }
    
    protected function registerBaseBindings()
    {
        static::setInstance($this);
        $this->instance('app', $this);
        $this->instance('Illuminate\Container\Container', $this);
    }
    
    protected function registerDatabaseBindings()
    {
        $this->singleton('db', function () {
            return $this->loadComponent(
                'database', 
                [
                    'Illuminate\Database\DatabaseServiceProvider',
                    'Illuminate\Pagination\PaginationServiceProvider' 
                ],
                'db'
            );
        });
    }
    
    protected function loadComponent($config, $providers, $return = null)
    {
        $this->configure($config);
        
        foreach ((array) $providers as $provider) {
            $this->register($provider);
        }

        return $this->make($return ?: $config);
    }
    
    public function register($provider, $options = [], $force = false)
    {
        if (! $provider instanceof ServiceProvider) {
            $provider = new $provider($this);
        }

        if (array_key_exists($providerName = get_class($provider), $this->loadedProviders)) {
            return;
        }

        $this->loadedProviders[$providerName] = true;

        $provider->register();
        $provider->boot();
    }

    public function configure($name)
    {
        if (isset($this->loadedConfigurations[$name])) {
            return;
        }

        $this->loadedConfigurations[$name] = true;

        $path = $this->getConfigurationPath($name);
        
        if ($path) {
            $this->make('config')->set($name, require $path);
        }
    }
}
