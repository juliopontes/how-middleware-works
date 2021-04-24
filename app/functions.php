<?php
use App\Core\Http\Response;
use App\Core\Http\Request;
use App\Core\Http\Router;

/**
 * Return request url path
 *
 * @return string
 */
function request_url_path()
{
    $request_uri = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
    $script_name = explode('/', trim($_SERVER['SCRIPT_NAME'], '/'));
    $parts = array_diff_assoc($request_uri, $script_name);
    if (empty($parts))
    {
        return '/';
    }

    $path = implode('/', $parts);

    if (($position = strpos($path, '?')) !== FALSE)
    {
        $path = substr($path, 0, $position);
    }

    return $path;
}

/**
 * @return Response
 */
function response()
{
    static $instance = null;

    if ($instance === null) {
        $instance = new Response();
    }

    return $instance;
}

function router()
{
    static $instance = null;

    if ($instance === null) {
        $instance = new Router();
    }

    return $instance;
}

/**
 * @return Request
 */
function request()
{
    static $instance = null;

    if ($instance === null) {
        $instance = new Request();
    }

    return $instance;
}

function base_path(string $path = '')
{
    if (!empty($path)) {
        $path = DIRECTORY_SEPARATOR . $path;
    }

    return BASE_PATH . $path;
}