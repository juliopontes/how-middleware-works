<?php
namespace App\Core;

use App\Core\Application\AbstractApplication;
use App\Middleware\Application\Check\MinimumRequirementsMiddleware;
use App\Middleware\Application\Routes\LoadRoutesMiddleware;
use App\Middleware\Application\Routes\DispatchRouterMiddleware;

class Application extends AbstractApplication
{
    public static function middlewares(): array
    {
        return [
            MinimumRequirementsMiddleware::class,
            LoadRoutesMiddleware::class,
            DispatchRouterMiddleware::class
        ];
    }
}