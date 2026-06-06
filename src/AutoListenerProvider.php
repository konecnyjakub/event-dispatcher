<?php
declare(strict_types=1);

namespace Konecnyjakub\EventDispatcher;

use Psr\Container\NotFoundExceptionInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

final class AutoListenerProvider implements ListenerProviderInterface
{
    public const int PRIORITY_HIGH = 100;
    public const int PRIORITY_NORMAL = 0;
    public const int PRIORITY_LOW = -100;

    /**
     * @var array<int, callable[]>
     */
    private array $listeners = [];

    public function __construct(
        private readonly ListenerValidator $listenerValidator = new ListenerValidator(),
        private readonly ?ContainerInterface $container = null
    ) {
    }

    /**
     * @return callable[]
     */
    public function getListenersForEvent(object $event): iterable
    {
        $listeners = $this->listeners;
        krsort($listeners);
        foreach ($listeners as $priority) {
            foreach ($priority as $callback) {
                /** @var class-string $classname */
                $classname = (string) $this->listenerValidator->getListenerReflection($callback)
                    ->getParameters()[0]
                    ->getType();
                if (!is_a($event, $classname)) {
                    continue;
                }

                yield $callback;
            }
        }
    }

    /**
     * @throws ReflectionException
     * @throws InvalidListenerException
     */
    public function addListener(callable $callback): void
    {
        $this->listenerValidator->validate($callback);

        $metadata = $this->getListenerMetadata($callback);

        $this->addListenerInternal($callback, $metadata);
    }

    /**
     * @param callable[]|object $callbacks
     * @throws ReflectionException
     * @throws InvalidListenerException
     */
    public function addListeners(iterable|object $callbacks): void
    {
        if (is_iterable($callbacks)) {
            foreach ($callbacks as $callback) {
                if (is_callable($callback)) {
                    $this->addListener($callback);
                }
            }
            return;
        }

        $reflectionClass = new ReflectionClass($callbacks);
        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if (count($method->getAttributes(Listener::class)) === 1) {
                /** @var callable $callback */
                $callback = [$callbacks, $method->name, ];
                $this->addListener($callback);
            }
        }
    }

    /**
     * @throws ReflectionException
     * @throws InvalidListenerException
     */
    public function addSubscriber(EventSubscriber $eventSubscriber): void
    {
        foreach ($eventSubscriber::getSubscribedEvents() as $className => $listeners) {
            foreach ($listeners as $listener) {
                /** @var callable $callback */
                $callback = [$eventSubscriber, $listener[0]];
                $this->listenerValidator->validate($callback, $className);
                $metadata = $this->getListenerMetadata($callback);
                if (isset($listener[1]) && $metadata->priority !== $listener[1]) {
                    $metadata = new Listener(priority: $listener[1]);
                }
                $this->addListenerInternal($callback, $metadata);
            }
        }
    }

    /**
     * @throws ContainerNotSetException
     * @throws InvalidListenerException
     * @throws ReflectionException
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function addServiceListener(string $serviceName, string $method = "__invoke"): void
    {
        if ($this->container === null) {
            throw new ContainerNotSetException();
        }
        try {
            $service = $this->container->get($serviceName);
        } catch (NotFoundExceptionInterface $e) {
            throw new InvalidListenerException(
                "The container does not have service '$serviceName'",
                InvalidListenerException::CODE_SERVICE_NOT_IN_CONTAINER,
                $e
            );
        }
        if (!is_object($service)) {
            throw new InvalidListenerException(
                "Service '$serviceName' is not an object",
                InvalidListenerException::CODE_WRONG_SERVICE_TYPE
            );
        }
        /** @var callable&(array{object, string}|object) $callback */
        $callback = $method === "__invoke" ? $service : [$service, $method];
        $this->listenerValidator->validate($callback);
        $metadata = $this->getListenerMetadata($callback);
        $reflection = $this->listenerValidator->getListenerReflection($callback);
        /** @var class-string $className */
        $className = (string) $reflection->getParameters()[0]->getType();
        $this->addListenerInternal($callback, $metadata);
    }

    private function addListenerInternal(callable $callback, Listener $metadata): void
    {
        if (!array_key_exists($metadata->priority, $this->listeners)) {
            $this->listeners[$metadata->priority] = [];
        }
        $this->listeners[$metadata->priority][] = $callback;
    }

    /**
     * @throws ReflectionException
     */
    private function getListenerMetadata(callable $callback): Listener
    {
        $attributes = $this->listenerValidator->getListenerMetadataReflection($callback)
            ->getAttributes(Listener::class);
        if (count($attributes) === 1) {
            /** @var Listener $attribute */
            $attribute = $attributes[0]->newInstance();
            return $attribute;
        }
        return new Listener();
    }
}
