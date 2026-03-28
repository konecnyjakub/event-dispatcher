<?php
declare(strict_types=1);

namespace Konecnyjakub\EventDispatcher;

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionNamedType;

final class ListenerValidator
{
    /**
     * @throws ReflectionException
     */
    public function getListenerReflection(callable $callback): ReflectionFunctionAbstract
    {
        if (is_object($callback) && !$callback instanceof Closure) {
            return new ReflectionMethod($callback, "__invoke");
        } elseif (is_array($callback)) {
            /** @var callable&array{0: class-string, 1: string} $callback */
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
     * @throws ReflectionException
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
     * @throws ReflectionException
     * @throws InvalidListenerException
     */
    public function validate(callable $callback, ?string $eventName = null): void
    {
        $reflection = $this->getListenerReflection($callback);
        if ($reflection->getNumberOfParameters() !== 1) {
            throw new InvalidListenerException(
                "The callback has to accept exactly 1 parameter",
                InvalidListenerException::CODE_WRONG_NUMBER_OF_PARAMETERS
            );
        }
        if (!class_exists((string) $reflection->getParameters()[0]->getType())) {
            throw new InvalidListenerException(
                "The callback's first parameter has to be a class name",
                InvalidListenerException::CODE_WRONG_PARAMETER_TYPE
            );
        }
        if ($eventName !== null && (string) $reflection->getParameters()[0]->getType() !== $eventName) {
            throw new InvalidListenerException(
                "The callback's first parameter has to be $eventName",
                InvalidListenerException::CODE_PARAMETER_NOT_EVENT
            );
        }
        if (
            !$reflection->getReturnType() instanceof ReflectionNamedType ||
            $reflection->getReturnType()->getName() !== "void"
        ) {
            throw new InvalidListenerException(
                "The callback's return type has to be explicitly set to void",
                InvalidListenerException::CODE_WRONG_RETURN_TYPE
            );
        }
    }
}
