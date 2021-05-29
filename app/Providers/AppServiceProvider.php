<?php

namespace App\Providers;

use App\Logger\CliRequestLogger;
use App\Logger\RequestLogger;
use App\PHPSandbox\Entrypoints\Clients\Client;
use Clue\React\Buzz\Browser;
use Illuminate\Support\ServiceProvider;
use Laminas\Uri\Uri;
use Laminas\Uri\UriFactory;
use React\EventLoop\Factory as LoopFactory;
use React\EventLoop\LoopInterface;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        UriFactory::registerScheme('chrome-extension', Uri::class);
    }

    public function register()
    {
        $this->loadConfigurationFile();

        $this->setMemoryLimit();

        $this->app->singleton(LoopInterface::class, function () {
            return LoopFactory::create();
        });

        $this->app->singleton(RequestLogger::class, function ($app) {
            return new RequestLogger($app->make(Browser::class), $app->make(CliRequestLogger::class));
        });

        $this->app->singleton(Client::class, Client::class);
    }

    protected function loadConfigurationFile()
    {
        $builtInConfig = config('expose');

        $localConfigFile = getcwd().DIRECTORY_SEPARATOR.'.expose.php';

        if (file_exists($localConfigFile)) {
            $localConfig = require $localConfigFile;
            config()->set('expose', array_merge($builtInConfig, $localConfig));

            return;
        }

        $configFile = implode(DIRECTORY_SEPARATOR, [
            $_SERVER['HOME'] ?? $_SERVER['USERPROFILE'] ?? __DIR__,
            '.expose',
            'config.php',
        ]);

        if (file_exists($configFile)) {
            $globalConfig = require $configFile;
            config()->set('expose', array_merge($builtInConfig, $globalConfig));
        }
    }

    protected function setMemoryLimit()
    {
        ini_set('memory_limit', config()->get('expose.memory_limit', '128M'));
    }
}
