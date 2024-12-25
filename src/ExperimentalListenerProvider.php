<?php
declare(strict_types=1);

namespace Konecnyjakub\EventDispatcher;

use Psr\EventDispatcher\ListenerProviderInterface;
use ReflectionException;

/**
 * @internal
 */
final class ExperimentalListenerProvider implements ListenerProviderInterface
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
     * @throws InvalidListenerException If the callback is not a valid event listener
     */
    public function addListener(callable $callback): void
    {
        $this->listenerValidator->validate($callback);

        $metadata = $this->getListenerMetadata($callback);

        /** @var class-string $classname */
        $classname = (string) $this->listenerValidator->getListenerReflection($callback)
            ->getParameters()[0]
            ->getType();

        $this->addListenerInternal($classname, $callback, $metadata->priority);
    }

    /**
     * @param callable[] $callbacks
     * @throws ReflectionException
     * @throws InvalidListenerException If the callback is not a valid event listener
     */
    public function addListeners(iterable $callbacks): void
    {
        foreach ($callbacks as $callback) {
            if (is_callable($callback)) {
                $this->addListener($callback);
            }
        }
    }

    public function addSubscriber(IEventSubscriber $eventSubscriber): void
    {
        foreach ($eventSubscriber::getSubscribedEvents() as $className => $listeners) {
            foreach ($listeners as $listener) {
                /** @var callable $callback */
                $callback = [$eventSubscriber, $listener[0]];
                $this->listenerValidator->validate($callback, $className);
                $metadata = $this->getListenerMetadata($callback);
                $this->addListenerInternal($className, $callback, $listener[1] ?? $metadata->priority);
            }
        }
    }

    /**
     * @param class-string $className
     */
    private function addListenerInternal(string $className, callable $callback, int $priority): void
    {
        if (!array_key_exists($className, $this->listeners)) {
            $this->listeners[$className] = [];
        }
        if (!array_key_exists($priority, $this->listeners[$className])) {
            $this->listeners[$className][$priority] = [];
        }
        $this->listeners[$className][$priority][] = $callback;
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
