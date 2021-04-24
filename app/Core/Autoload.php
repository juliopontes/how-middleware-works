<?php
namespace App\Core;

abstract class Autoload
{
    /**
     * Locator List
     *
     * @var array
     */
    protected static $locator = [
        'namespaces' => [],
        'prefixes' => [],
        'classes' => [],
    ];

    /**
     * File name
     *
     * @var String
     */
    protected static $file = '';

    /**
     * Registers the loader with the PHP autoloader.
     *
     * @param Boolean $prepend Whether to prepend the autoloader or not
     *
     * @see \spl_autoload_register();
     */
    public static function register($prepend = false)
    {
        if (!defined('NAMESPACE_SEPARATOR')) {
            define('NAMESPACE_SEPARATOR','\\');
        }

        spl_autoload_register([__CLASS__, 'loadClass'], true, $prepend);
    }

    /**
     * Unregisters the loader with the PHP autoloader.
     *
     * @see \spl_autoload_unregister();
     */
    public static function unregister()
    {
        spl_autoload_unregister([__CLASS__, 'loadClass']);
    }

    /**
     * Register a namespace
     *
     * @param string $namespace
     * @param string|array $paths The location(s) of the namespace
     *
     * @since 1.0.0
     */
    public static function registerNamespace($namespace, $paths)
    {
        settype($paths,'array');
        $namespace = trim($namespace, NAMESPACE_SEPARATOR);
        self::$locator['namespaces'][NAMESPACE_SEPARATOR.$namespace] = $paths;

        krsort(self::$locator['namespaces'], SORT_STRING);
    }

    /**
     * Register a alias for a class
     *
     * @param string $class_name
     * @param string $class_alias
     * @param bool $autoload
     *
     * @since 1.0.0
     */
    public static function registerClassAlias($class_name, $class_alias, $autoload = true)
    {
        class_alias($class_name, $class_alias, $autoload);
    }

    /**
     * Registers an array of namespaces
     *
     * @param array $namespaces An array of namespaces (namespaces as keys and locations as values)
     *
     * @since 1.0.0
     */
    public static function registerNamespaces(array $namespaces)
    {
        foreach ($namespaces as $namespace => $paths)
        {
            $namespace = trim($namespace, NAMESPACE_SEPARATOR);
            self::$locator['namespaces'][NAMESPACE_SEPARATOR.$namespace] = (array) $paths;
        }

        krsort(self::$locator['namespaces'], SORT_STRING);
    }

    /**
     * register prefix to autoload
     *
     * @param $prefix
     * @param $path
     *
     * @since 1.0.0
     */
    public static function registerPrefix($prefix, $path)
    {
        $prefix = trim($prefix);

        self::$locator['prefixes'][$prefix] = (array) $path;

        krsort(self::$locator['prefixes'], SORT_STRING);
    }

    /**
     * Register Array of prefixes
     *
     * @param array $prefixes
     *
     * @since 1.0.0
     */
    public static function registerPrefixes(array $prefixes)
    {
        foreach ($prefixes as $prefix => $paths)
        {
            $prefix = trim($prefix);
            self::$locator['prefixes'][$prefix] = (array) $paths;
        }

        krsort(self::$locator['prefixes'], SORT_STRING);
    }

    /**
     * Register a single Class
     *
     * @param $class_name
     * @param $path
     *
     * @since 1.0.0
     */
    public static function registerClass($class_name, $path)
    {
        $class_name = trim($class_name);
        $path = trim($path);

        self::$locator['classes'][$class_name] = $path;

        krsort(self::$locator['classes'], SORT_STRING);
    }

    /**
     * Register a list of classes
     *
     * @param array $classes
     *
     * @since 1.0.0
     */
    public static function registerClasses(array $classes)
    {
        foreach ($classes as $class_name => $path)
        {
            self::$locator['classes'][$class_name] = $path;
        }

        krsort(self::$locator['classes'], SORT_STRING);
    }

    /**
     * Autoload a camelCase class
     *
     * @param $class_name
     *
     * @since 1.0.0
     */
    public static function loadClass($class_name)
    {
        $result = true;

        if (!self::isDeclared($class_name)) {
            //Get the path
            $path = self::findPath( $class_name );

            if ($path !== false) {
                if (substr($path,-4) == '.php') {
                    $result = self::loadFile($path);
                } else {
                    $parts = explode(NAMESPACE_SEPARATOR, $class_name);
                    array_shift($parts);

                    if (count($parts) == 1) {
                        $tmp = DIRECTORY_SEPARATOR . end($parts);
                    } else {
                        $tmp = implode(DIRECTORY_SEPARATOR,$parts);
                    }
                    $file_path = $path . DIRECTORY_SEPARATOR . $tmp . '.php';

                    $result = self::loadFile($file_path);
                }
            } else {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Get the path based on a class name
     *
     * @param string $class The class name
     * @return string|false Returns canonicalized absolute pathname or FALSE of the class could not be found.
     *
     * @since 1.0.0
     */
    public static function findPath(&$class)
    {
        $class_path = false;

        $class_path = self::findClassPath($class, self::$locator['prefixes'], true);

        if (strpos($class,NAMESPACE_SEPARATOR) === false) {
            if (!$class_path) {
                $class_path = self::findClassPath($class, self::$locator['classes']);
            }
        } else {
            if (!$class_path) {
                $class_path = self::findClassPath($class, self::$locator['namespaces']);
            }
        }

        if (is_array($class_path) && count($class_path) == 1) {
            $class_path = end($class_path);
        }

        return $class_path;
    }

    /**
     * Return class path by array check
     *
     * @param $class_name
     * @param $data
     *
     * @return bool
     *
     * @since 1.0.0
     */
    private static function findClassPath(&$class_name, $data, $remove_prefix = false)
    {
        if (empty($data)) {
            return false;
        }

        self::$file = null;
        $class_path = false;
        $keys = array_keys($data);

        foreach ($keys as $key) {
            $keyTrim = ltrim($key,NAMESPACE_SEPARATOR);

            if (substr($class_name,0, strlen($keyTrim)) != $keyTrim) continue;
            self::$file = substr($class_name,strlen($key));

            $class_path = $data[$key];
            if ($remove_prefix) {
                $class_name = str_replace($keyTrim,'',$class_name);
            }
            break;
        }

        return $class_path;
    }

    /**
     * Load a class based on a path
     *
     * @param string $path The file path
     *
     * @return boolean Returns TRUE if the file could be loaded, otherwise returns FALSE.
     *
     * @since 1.0.0
     */
    public static function loadFile($path)
    {
        $path = str_replace('//' , '/', $path);
        $path = str_replace(NAMESPACE_SEPARATOR,DIRECTORY_SEPARATOR, $path);

        //Don't re-include files and stat the file if it exists.
        if (!in_array($path, get_included_files()) && is_readable($path)) {
            require_once $path;
        }

        return true;
    }

    /**
     * Tells if a class, interface or trait exists.
     *
     * @params string $class_name
     *
     * @return boolean
     *
     * @since 1.0.0
     */
    public static function isDeclared($class_name)
    {
        return class_exists($class_name, false)
            || interface_exists($class_name, false)
            || trait_exists($class_name, false);
    }
}

// register autoloader
Autoload::register();