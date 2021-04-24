<?php

namespace App\Core\Middleware;

use App\Core\Http\Request;

class MiddlewareStack
{
    protected $start;
    
    public function __construct()
    {
        $this->start = function () {};
    }

    public function add($middleware)
    {
        $next = $this->start;

        $this->start = function () use ($middleware, $next) { return $middleware($next); };

        return $this;
    }

    public function handle()
    {
        return call_user_func_array($this->start, func_get_args());
    }
}
