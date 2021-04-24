<?php

namespace App\Middleware\Route;

use App\Core\Http\Request;
use App\Core\Middleware\Interfaces\RouteMiddlewareInterface;

class PageResponderMiddleware implements RouteMiddlewareInterface
{
    public function __invoke(Request $request, callable $next)
    {
        return response();
    }
}