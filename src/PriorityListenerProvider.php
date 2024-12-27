<?php
declare(strict_types=1);

namespace Konecnyjakub\EventDispatcher;

use Deprecated;
use Psr\EventDispatcher\ListenerProviderInterface;

/**
 * @deprecated Use {@see AutoListenerProvider} instead
 */
final class PriorityListenerProvider implements ListenerProviderInterface
{
    public const int PRIORITY_HIGH = 100;
    public const int PRIORITY_NORMAL = 0;
    public const int PRIORITY_LOW = -100;

    /**
     * @var array<class-string, array<int, callable[]>>
     */
    private array $listeners = [];

    /**
     * @param class-string $className
     */
    #[Deprecated("use method AutoListenerProvider::addListener instead", "1.5")]
    public function registerListener(string $className, callable $callback, int $priority = self::PRIORITY_NORMAL): void
    {
        $this->addListener(...func_get_args()); // @phpstan-ignore argument.type
    }

    /**
     * @param class-string $className
     */
    #[Deprecated("use method AutoListenerProvider::addListener instead", "1.5")]
    public function addListener(string $className, callable $callback, int $priority = self::PRIORITY_NORMAL): void
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
     * @param class-string $classname
     * @param callable[] $callbacks
     */
    #[Deprecated("use method AutoListenerProvider::addListeners instead", "1.5")]
    public function addListeners(string $classname, iterable $callbacks, int $priority = self::PRIORITY_NORMAL): void
    {
        foreach ($callbacks as $callback) {
            if (is_callable($callback)) {
                $this->addListener($classname, $callback, $priority);
            }
        }
    }

    #[Deprecated("use method AutoListenerProvider::addSubscriber instead", "1.5")]
    public function addSubscriber(IEventSubscriber $eventSubscriber): void
    {
        foreach ($eventSubscriber::getSubscribedEvents() as $className => $listeners) {
            foreach ($listeners as $listener) {
                /** @var callable $callback */
                $callback = [$eventSubscriber, $listener[0]];
                $this->addListener($className, $callback, $listener[1] ?? self::PRIORITY_NORMAL);
            }
        }
    }

    #[Deprecated("use method AutoListenerProvider::getListenersForEvent instead", "1.5")]
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
}
