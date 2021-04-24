<?php

namespace App\Core\Middleware\Interfaces;

interface ApplicationMiddlewareInterface
{
    public function __invoke(callable $next);
}