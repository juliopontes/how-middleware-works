<?php

namespace App\Core\Application;

use App\Core\Middleware\MiddlewareStack;

abstract class AbstractApplication
{
    public static function middlewares(): array
    {
        return [];
    }

    /**
     * Run middlewares
     */
    public static function run()
    {
        $app = new MiddlewareStack();

        foreach (array_reverse(static::middlewares()) as $middleware) {
            $app->add(new $middleware);
        }

        $app->handle();
    }
}
