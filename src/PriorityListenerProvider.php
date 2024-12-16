<?php
declare(strict_types=1);

namespace Konecnyjakub\EventDispatcher;

use Psr\EventDispatcher\ListenerProviderInterface;

final class PriorityListenerProvider implements ListenerProviderInterface
{
    private array $listeners = [];

    /**
     * @param class-string $className
     */
    public function registerListener(string $className, callable $callback, int $priority): void
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
    public function registerListeners(string $classname, iterable $callbacks, int $priority): void
    {
        foreach ($callbacks as $callback) {
            if (is_callable($callback)) {
                $this->registerListener($classname, $callback, $priority);
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
