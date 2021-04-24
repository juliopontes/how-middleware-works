# How it works?

A application run

## Example Application

An application could have many middleware as you want. See this example below:

```
class Application extends AbstractApplication
{
    public static function middlewares(): array
    {
        return [
            App\Middleware\Application\Check\MinimumRequirementsMiddleware::class,
            App\Middleware\Application\LoadRoutesMiddleware::class,
            App\Middleware\Application\DispatchRouterMiddleware::class
        ];
    }
}
```

# Application Middleware

An application middleware receive callable $next Middleware to resolve.

```
/***
 * This middleware check validate server to run this application
 **/
class MinimumRequirementsMiddleware implements ApplicationMiddlewareInterface
{
    public function __invoke(callable $nextMiddleware)
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

        return $nextMiddleware();
    }
}
```

```
/***
 * This middleware check filesystem permission for application
 **/
class FilesystemPermissionsMiddleware implements ApplicationMiddlewareInterface
{
    public function __invoke(callable $nextMiddleware)
    {
        $directories = [
            'config',
            'tmp'
        ];
        
        foreach ($directories as $directory) {
            // return path to that directory
            $path = base_path($directory);
            
            if ( !is_writable($path) ) {
                die('[Filesystem] folder "' . $directory . '" (' . $path . ') is NOT WRITEABLE');
            }
        }

        return $nextMiddleware();
    }
}
```

You can have many middlewares as you want.

```
class Application extends AbstractApplication
{
    public static function middlewares(): array
    {
        return [
            App\Middleware\Application\Check\MinimumRequirementsMiddleware::class,
            App\Middleware\Application\Check\FilesystemPermissionsMiddleware::class,
            App\Middleware\Application\Check\DatabaseConnectionMiddleware::class,            
            ...
            App\Middleware\Application\Routes\LoadRoutesMiddleware::class,
            App\Middleware\Application\Routes\DispatchRouterMiddleware::class
        ];
    }
}
```

