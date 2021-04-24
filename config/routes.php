<?php
use App\Core\Middleware\MiddlewareStack;
use App\Core\Http\Request;
use App\Middleware\Route\PageResponderMiddleware;

$router = router();

$router->pattern('id', '\d+');

$router->add('/','App\Actions\Home');