<?php
declare(strict_types=1);

namespace Konecnyjakub\EventDispatcher;

use Psr\EventDispatcher\ListenerProviderInterface;

final class PriorityListenerProvider implements ListenerProviderInterface
{
    private array $listeners = [];

    /**
     * @param class-string $className
     * @deprecated Use {@see self::addListener()} instead
     */
    public function registerListener(string $className, callable $callback, int $priority = 0): void
    {
        $this->addListener(...func_get_args());
    }

    /**
     * @param class-string $className
     */
    public function addListener(string $className, callable $callback, int $priority = 0): void
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
    public function addListeners(string $classname, iterable $callbacks, int $priority = 0): void
    {
        foreach ($callbacks as $callback) {
            if (is_callable($callback)) {
                $this->addListener($classname, $callback, $priority);
            }
        }
    }

    public function addSubscriber(IEventSubscriber $eventSubscriber): void
    {
        foreach ($eventSubscriber::getSubscribedEvents() as $className => $listeners) {
            foreach ($listeners as $listener) {
                /** @var callable $callback */
                $callback = [$eventSubscriber, $listener[0]];
                $this->addListener($className, $callback, $listener[1] ?? 0);
            }
        }
    }

    public function getListenersForEvent(object $event): iterable
    {
        $result = [];
        $listeners = $this->listeners[$event::class] ?? [];
        krsort($listeners);
        foreach ($listeners as $priority) {
            foreach ($priority as $callback) {
                $result[] = $callback;
            }
        }
        return $result;
    }
}
