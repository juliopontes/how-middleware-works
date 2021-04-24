<?php
namespace App\Core\Http;

class Request
{
    /**
     * Request parameters.
     *
     * @var array
     */
    protected $params = [];

    /**
     * @var string
     */
    protected $pathInfo;

    /**
     * @var string
     */
    protected $requestUri;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var string
     */
    protected $basePath;

    /**
     * @var string
     */
    protected $method;

    /**
     * Request constructor.
     */
    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'];

        $this->pathInfo = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $dirname = dirname($_SERVER['SCRIPT_NAME']);
        if ($dirname != '/') {
            $this->pathInfo = str_replace($dirname,'', $this->pathInfo);
        }

        if (empty($this->pathInfo)) {
            $this->pathInfo = '/';
        } elseif (!$this->pathInfo != '/') {
            $this->pathInfo = '/' . $this->pathInfo . '/';
        }
        // clean multiple / in url
        $this->pathInfo = preg_replace('/\/+/', '/', $this->pathInfo);

        // merge params
        switch ($this->method) {
            case 'GET':
                $this->params = $_GET;
                break;
            case 'POST':
                $this->params = $_POST;

                if (!empty($_GET)) {
                    $this->params = array_merge($_GET, $this->params);
                }
                break;
        }

    }

    /**
     * Full requested url
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function url()
    {
        $page_url = 'http';
        if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {$page_url .= "s";}
        $page_url .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $page_url .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
        } else {
            $page_url .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
        }
        return $page_url;
    }

    /**
     * @return string
     */
    public function path()
    {
        return $this->pathInfo;
    }

    /**
     * @return string
     */
    public function method()
    {
        return $this->method;
    }

    /**
     * @param string $verb
     * @return bool
     */
    public function isMethod(string $verb)
    {
        return $_SERVER['REQUEST_METHOD'] == strtoupper($verb);
    }

    /**
     * @param array $parameters
     * @return $this
     */
    public function bindParams(array $parameters)
    {
        $this->params = array_merge($this->params, $parameters);

        return $this;
    }

    /**
     * @param string $name
     * @param null $default
     * @return mixed|null
     */
    public function getParam(string $name, $default = null)
    {
        $result = $this->params[$name] ?? $default;
        return is_string($result) ? trim($result) : $result;
    }

    public function setParam(string $name, $value)
    {
        $this->params[$name] = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        $result = [];
        foreach ($this->params as $param => $value) {
            if (is_string($this->params[$param])) {
                $this->params[$param] = trim($value);
            }
        }

        return $this->params;
    }

    /**
     * @param string ...$name
     */
    public function only(string ...$name)
    {
        $result = [];

        foreach (func_get_args() as $param) {
            $result[$param] = $this->getParam($param);
        }

        return array_map('trim', $result);
    }

    /**
     * @return bool
     */
    public function isAjax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
}