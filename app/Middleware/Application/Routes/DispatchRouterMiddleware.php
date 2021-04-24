<?php

namespace App\Middleware\Application\Routes;

use App\Core\Middleware\Interfaces\ApplicationMiddlewareInterface;

class DispatchRouterMiddleware implements ApplicationMiddlewareInterface
{
    public function __invoke(callable $next)
    {
        router()->dispatch(request_url_path());

        return $next;
    }
}
