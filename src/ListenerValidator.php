<?php
declare(strict_types=1);

namespace Konecnyjakub\EventDispatcher;

use Closure;
use ReflectionClass;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionNamedType;

/**
 * @internal
 */
final class ListenerValidator
{
    /**
     * @throws \ReflectionException
     */
    public function getListenerReflection(callable $callback): ReflectionFunctionAbstract
    {
        if (is_object($callback) && !$callback instanceof Closure) {
            return new ReflectionMethod($callback, "__invoke");
        } elseif (is_array($callback)) {
            // @phpstan-ignore argument.type, argument.type
            return new ReflectionMethod($callback[0], $callback[1]);
        } else {
            /** @var (Closure|string)&callable $callback */
            return new ReflectionFunction($callback);
        }
    }

    /**
     * @template T of object
     * @param callable|(T&callable) $callback
     * @return ($callback is object ? ReflectionClass<T> : ReflectionFunctionAbstract)
     * @throws \ReflectionException
     */
    public function getListenerMetadataReflection(callable $callback): ReflectionFunctionAbstract|ReflectionClass
    {
        if (is_object($callback) && !$callback instanceof Closure) {
            /** @var ReflectionClass<T> $reflection */
            $reflection = new ReflectionClass($callback);
            return $reflection;
        }
        return $this->getListenerReflection($callback);
    }

    /**
     * @throws \ReflectionException
     * @throws InvalidListenerException If the callback is not a valid event listener
     */
    public function validate(callable $callback): void
    {
        $reflection = $this->getListenerReflection($callback);
        if ($reflection->getNumberOfParameters() !== 1) {
            throw new InvalidListenerException("The callback has to accept exactly 1 parameter");
        }
        if (!class_exists((string) $reflection->getParameters()[0]->getType())) {
            throw new InvalidListenerException("The callback's first parameter has to be a class name");
        }
        if (
            !$reflection->getReturnType() instanceof ReflectionNamedType ||
            $reflection->getReturnType()->getName() !== "void"
        ) {
            throw new InvalidListenerException("The callback's return type has to explicitly set to void");
        }
    }
}
