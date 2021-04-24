<?php

namespace App\Middleware\Application\Check;

use App\Core\Middleware\Interfaces\ApplicationMiddlewareInterface;

class MinimumRequirementsMiddleware implements ApplicationMiddlewareInterface
{
    public function __invoke(callable $next)
    {
        define('APP_MINIMUM_PHP', '7.4');

        if (version_compare(PHP_VERSION, APP_MINIMUM_PHP, '<'))
        {
            die('Your host needs to use PHP ' . APP_MINIMUM_PHP . ' or higher to run this application,');
        }

        if ( !extension_loaded('pdo') )
        {
            die('PDO is required');
        }

        return $next();
    }
}
