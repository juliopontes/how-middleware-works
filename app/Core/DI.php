<?php
namespace App\Core;

use ReflectionClass;
use ReflectionFunction;
use ReflectionParameter;
use ReflectionException;
use Closure;
use BadMethodCallException;
use Exception;

abstract class DI
{
    /**
     * @var array
     */
    protected static $arguments = [];

    /**
     * @var array
     */
    protected static $singletonClasses = [];

    /**
     * @param string $name
     * @param $value
     */
    public static function setArgument(string $name, $value)
    {
        self::$arguments[$name] = $value;
    }

    /**
     * @param string $class_name
     */
    public static function registerSingletonClass(string $class_name)
    {
        self::$singletonClasses[$class_name] = '';
    }

    /**
     * @param $parameters
     * @param array $arguments
     * @return array
     * @throws Exception
     * @throws ReflectionException
     */
    public static function getParameters($parameters, array $arguments = [])
    {
        /**
         * @var  $key
         * @var ReflectionParameter $parameter
         */
        foreach ($parameters as $key => $parameter) {
            if (isset($arguments[$parameter->getName()])) {
                $parameters[$key] = $arguments[$parameter->getName()];
            } elseif ($class = $parameter->getClass()) {
                $parameters[$key] = self::createClass($class);
            } elseif ($parameter->isDefaultValueAvailable()) {
                $parameters[$key] = $parameter->getDefaultValue();
            } elseif (!empty($arguments)) {
                $parameters[$key] = array_shift($arguments);
            } else {
                throw new Exception("Can not resolve class dependency {$parameter->name}");
            }
        }

        return $parameters;
    }

    /**
     * @param string|ReflectionClass $class
     * @param array $arguments
     * @return object
     * @throws Exception
     * @throws ReflectionException
     */
    public static function createClass($class, array $arguments = [])
    {
        if (!($class instanceof ReflectionClass)) {
            $class = new ReflectionClass($class);
        }

        if (!$class->isInstantiable()) {
            throw new Exception("Class {$class->getShortName()} is not instantiable");
        }

        $className = $class->getName();

        if (!empty(self::$singletonClasses[$className]) && isset(self::$singletonClasses[$className])) {
            return self::$singletonClasses[$className];
        }

        if ($constructor = $class->getConstructor()) {
            $classArguments = $class->getConstructor()->getParameters();
            $argumentsObjects = array();
            foreach ($classArguments as $classArgument) {
                if (isset($arguments[$classArgument->getName()])) {
                    $argumentsObjects[] = $arguments[ $classArgument->getName() ];
                    unset($arguments[ $classArgument->getName() ]);
                } elseif ($classArgumentClass = $classArgument->getClass()) {
                    $argumentsObjects[] = self::createClass($classArgumentClass, $arguments);
                } elseif (!$classArgument->isOptional()) {
                    throw new Exception(sprintf('argument %s is required for create class %s', $classArgument->getName(),$class->getName()));
                } elseif (!empty($arguments)) {
                    $argumentsObjects[] = array_shift($arguments);
                }
            }

            $instance = $class->newInstanceArgs($argumentsObjects);
        } else {
            $instance = new $className();
        }

        if (empty(self::$singletonClasses[$className]) && isset(self::$singletonClasses[$className])) {
            self::$singletonClasses[$className] = $instance;
        }

        return $instance;
    }

    /**
     * @param string|Closure $fn
     * @param array $params
     * @return mixed
     * @throws Exception
     * @throws ReflectionException
     */
    public static function call($fn, array $params = [])
    {
        if (($fn instanceof Closure)) {
            return call_user_func_array($fn, self::getParameters((new ReflectionFunction($fn))->getParameters(), $params));
        } elseif (is_string($fn)) {
            if (stripos($fn, '@') !== false) {
                list($class, $method) = explode('@', $fn);
            } else {
                $class = $fn;
                $method = '__invoke';
            }

            $rc = new ReflectionClass($class);

            if (!$rc->isInstantiable()) {
                throw new Exception("Class {$class} is not instantiable");
            }

            if (!$rc->hasMethod($method)) {
                throw new BadMethodCallException(sprintf('Class %s method %s not exists.',$rc->getShortName(), $method));
            }

            return call_user_func_array([self::createClass($class), $method], self::getParameters($rc->getMethod($method)->getParameters(), $params));
        } else {
            throw new Exception('$fn must be a String or Closure.');
        }
    }
}