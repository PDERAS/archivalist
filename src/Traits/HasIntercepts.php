<?php

namespace Pderas\Archivalist\Traits;

use ReflectionMethod;
use ReflectionClass;

trait HasIntercepts
{
    /**
     * Magic method to intercept & forward calls to the
     * main query
     *
     * @param string $method
     * @param array $args
     *
     * @return mixed
     */
    public function __call(string $method, array $args)
    {
        // get the intercept name: `update()` => `updateArchiveIntercept()`
        $interceptMethod = $this->getIntercept($method);

        // Checks to see if the function exists on the current class
        if ($this->hasIntercept($interceptMethod)) {
            // run the function, forwarding the arguments
            $this->{$interceptMethod}(...$args);
        }

        // Run the original method, unchanged
        $this->query = $this->query->{$method}(...$args);

        return $this;
    }

    /**
     * Formats the intercept method name
     *
     * @param string $method
     *
     * @return string
     */
    protected function getIntercept(string $method): string
    {
        return "intercept" . ucfirst($method);
    }

    /**
     * Checks to see if the current class contains an available intercept
     *
     * @param string $interceptMethod
     *
     * @return string
     */
    protected function hasIntercept(string $interceptMethod): string
    {
        $class = new ReflectionClass(self::class);
        return collect($class->getMethods(ReflectionMethod::IS_PROTECTED))
            ->some(function ($method) use ($interceptMethod) {
                return $method->name === $interceptMethod;
            });
    }
}
