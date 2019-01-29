<?php

namespace LaravelAux;

use Illuminate\Support\ServiceProvider;
use LaravelAux\Commands\MakeCrudCommand;

class LaravelAuxServiceProvider extends ServiceProvider
{
    /**
     * Service Provider register method
     */
    public function register()
    {
        $this->commands([
            MakeCrudCommand::class
        ]);
    }

    /**
     * Service Provider boot actions
     */
    public function boot()
    {
        // Do nothing
    }
}