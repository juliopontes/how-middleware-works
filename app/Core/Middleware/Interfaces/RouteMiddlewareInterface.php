<?php

namespace App\Core\Middleware\Interfaces;

use App\Core\Http\Request;

interface RouteMiddlewareInterface
{
    public function __invoke(Request $request, callable $next);
}