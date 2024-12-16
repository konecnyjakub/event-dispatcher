<?php
declare(strict_types=1);

namespace Konecnyjakub\EventDispatcher;

use Psr\EventDispatcher\ListenerProviderInterface;

/**
 * @deprecated Use {@see PriorityListenerProvider} instead
 */
final class ListenerProvider implements ListenerProviderInterface
{
    private PriorityListenerProvider $listenerProvider;

    public function __construct()
    {
        $this->listenerProvider = new PriorityListenerProvider();
    }

    /**
     * @param class-string $className
     */
    public function registerListener(string $className, callable $callback): void
    {
        $this->listenerProvider->registerListener(...func_get_args());
    }

    /**
     * @param class-string $classname
     * @param callable[] $callbacks
     */
    public function registerListeners(string $classname, iterable $callbacks): void
    {
        $this->listenerProvider->registerListeners(...func_get_args());
    }

    public function addSubscriber(IEventSubscriber $eventSubscriber): void
    {
        foreach ($eventSubscriber::getSubscribedEvents() as $className => $listeners) {
            foreach ($listeners as $listener) {
                /** @var callable $callback */
                $callback = [$eventSubscriber, $listener[0]];
                $this->registerListener($className, $callback);
            }
        }
    }

    public function getListenersForEvent(object $event): iterable
    {
        return $this->listenerProvider->getListenersForEvent(...func_get_args());
    }
}
