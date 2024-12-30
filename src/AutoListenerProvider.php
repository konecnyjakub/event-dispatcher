<?php
declare(strict_types=1);

namespace Konecnyjakub\EventDispatcher;

use Psr\EventDispatcher\ListenerProviderInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

final class AutoListenerProvider implements ListenerProviderInterface
{
    public const int PRIORITY_HIGH = 100;
    public const int PRIORITY_NORMAL = 0;
    public const int PRIORITY_LOW = -100;

    /**
     * @var array<class-string, array<int, callable[]>>
     */
    private array $listeners = [];

    public function __construct(private readonly ListenerValidator $listenerValidator = new ListenerValidator())
    {
    }

    public function getListenersForEvent(object $event): iterable
    {
        $listeners = $this->listeners[$event::class] ?? [];
        krsort($listeners);
        foreach ($listeners as $priority) {
            foreach ($priority as $callback) {
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

        /** @var class-string $classname */
        $classname = (string) $this->listenerValidator->getListenerReflection($callback)
            ->getParameters()[0]
            ->getType();

        $this->addListenerInternal($classname, $callback, $metadata);
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
    public function addSubscriber(IEventSubscriber $eventSubscriber): void
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
                $this->addListenerInternal($className, $callback, $metadata);
            }
        }
    }

    /**
     * @param class-string $className
     */
    private function addListenerInternal(string $className, callable $callback, Listener $metadata): void
    {
        if (!array_key_exists($className, $this->listeners)) {
            $this->listeners[$className] = [];
        }
        if (!array_key_exists($metadata->priority, $this->listeners[$className])) {
            $this->listeners[$className][$metadata->priority] = [];
        }
        $this->listeners[$className][$metadata->priority][] = $callback;
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
