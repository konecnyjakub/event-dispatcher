<?php
declare(strict_types=1);

namespace Konecnyjakub\EventDispatcher;

use Deprecated;
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
    #[Deprecated("use method PriorityListenerProvider::addListener instead", "1.2")]
    public function registerListener(string $className, callable $callback): void
    {
        $this->listenerProvider->addListener(...func_get_args()); // @phpstan-ignore argument.type
    }

    /**
     * @param class-string $classname
     * @param callable[] $callbacks
     */
    #[Deprecated("use method PriorityListenerProvider::addListeners instead", "1.2")]
    public function registerListeners(string $classname, iterable $callbacks): void
    {
        $this->listenerProvider->addListeners(...func_get_args()); // @phpstan-ignore argument.type
    }

    #[Deprecated("use method PriorityListenerProvider::addSubscriber instead", "1.2")]
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

    #[Deprecated("use method PriorityListenerProvider::getListenersForEvent instead", "1.2")]
    public function getListenersForEvent(object $event): iterable
    {
        return $this->listenerProvider->getListenersForEvent(...func_get_args()); // @phpstan-ignore argument.type
    }
}
