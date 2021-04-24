<?php
namespace App\Core\Http;

use App\Core\DI;

class Router
{
    /**
     * Associative array of routes (the routing table)
     * @var array
     */
    protected $routes = [
        'dynamic' => [],
        'static' => []
    ];

    /**
     * Patterns
     * @var array
     */
    protected $patterns = [];

    /**
     * Action to execute
     * @var string
     */
    protected string $action;

    /**
     * @param string $name
     * @param string $regex
     * @return $this
     */
    public function pattern(string $name, string $regex)
    {
        $this->patterns[$name] = $regex;

        return $this;
    }

    /**
     * Add a route to the routing table
     *
     * @param string $route  The route URL
     * @param string $class class
     */
    public function add(string $route, string $class)
    {
        /** @todo fix regex? */
        preg_match_all('/(\/{.*})/', $route, $match);
        if (!empty($match[0])) {
            // Convert the route to a regular expression: escape forward slashes
            $route = preg_replace('/\//', '\\/', $route);

            // replace patterns
            $route = preg_replace_callback('/\{([a-z]+)\}/', function($match) {
                return sprintf('{%s:%s}', $match[1], $this->patterns[$match[1]]);
            }, $route);

            // Convert variables e.g. {controller}
            $route = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[a-z-]+)', $route);

            // Convert variables with custom regular expressions e.g. {id:\d+}
            $route = preg_replace('/\{([a-z]+):([^\}]+)\}/', '(?P<\1>\2)', $route);

            // Add start and end delimiters, and case insensitive flag
            $route = '/^' . $route . '$/i';

            $this->routes['dynamic'][$route] = $class;
        } else {
            $this->routes['static'][$route] = $class;
        }
    }

    /**
     * Get all the routes from the routing table
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Match the route to the routes in the routing table, setting the $params
     * property if a route is found.
     *
     * @param string $url The route URL
     *
     * @return boolean  true if a match found, false otherwise
     */
    public function match($url)
    {
        $request = request();

        if (isset($this->routes['static'][$url])) {
            $this->action = $this->routes['static'][$url];
            return true;
        }

        foreach ($this->routes['dynamic'] as $route => $action) {
            if (preg_match($route, $url, $matches)) {
                $params = [];
                // Get named capture group values
                foreach ($matches as $key => $match) {
                    if (is_string($key)) {
                        $request->setParam($key, $match);
                    }
                }

                $this->action = $action;
                return true;
            }
        }

        return false;
    }

    /**
     * Dispatch the route, creating the controller object and running the
     * action method
     *
     * @param string $url The route URL
     *
     * @return void
     */
    public function dispatch($url)
    {
        $url = $this->removeQueryStringVariables($url);

        if ($this->match($url)) {
            if (class_exists($this->action)) {
                echo DI::call($this->action);
            } else {
                throw new \Exception("class $this->action not found");
            }
        } else {
            throw new \Exception('No route matched.', 404);
        }
    }

    /**
     * Remove the query string variables from the URL (if any). As the full
     * query string is used for the route, any variables at the end will need
     * to be removed before the route is matched to the routing table. For
     * example:
     *
     *   URL                           $_SERVER['QUERY_STRING']  Route
     *   -------------------------------------------------------------------
     *   localhost                     ''                        ''
     *   localhost/?                   ''                        ''
     *   localhost/?page=1             page=1                    ''
     *   localhost/posts?page=1        posts&page=1              posts
     *   localhost/posts/index         posts/index               posts/index
     *   localhost/posts/index?page=1  posts/index&page=1        posts/index
     *
     * A URL of the format localhost/?page (one variable name, no value) won't
     * work however. (NB. The .htaccess file converts the first ? to a & when
     * it's passed through to the $_SERVER variable).
     *
     * @param string $url The full URL
     *
     * @return string The URL with the query string variables removed
     */
    protected function removeQueryStringVariables($url)
    {
        if ($url != '') {
            $parts = explode('&', $url, 2);

            if (strpos($parts[0], '=') === false) {
                $url = $parts[0];
            } else {
                $url = '';
            }
        }

        return $url;
    }
}