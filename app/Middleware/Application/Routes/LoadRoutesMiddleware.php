<?php

namespace App\Middleware\Application\Routes;

use App\Core\Middleware\Interfaces\ApplicationMiddlewareInterface;

class LoadRoutesMiddleware implements ApplicationMiddlewareInterface
{
    public function __invoke(callable $next)
    {
        $routes_file = base_path('config\routes.php');

        require_once $routes_file;

        return $next();
    }
}
