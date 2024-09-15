<?php
declare(strict_types=1);

namespace Konecnyjakub\EventDispatcher;

use Psr\EventDispatcher\ListenerProviderInterface;

final class ListenerProvider implements ListenerProviderInterface
{
    /**
     * @var array<class-string, callable[]>
     */
    private array $listeners = [];

    /**
     * @param class-string $className
     */
    public function registerListener(string $className, callable $callback): void
    {
        if (!array_key_exists($className, $this->listeners)) {
            $this->listeners[$className] = [];
        }
        $this->listeners[$className][] = $callback;
    }

    /**
     * @param class-string $classname
     * @param callable[] $callbacks
     */
    public function registerListeners(string $classname, iterable $callbacks): void
    {
        foreach ($callbacks as $callback) {
            if (is_callable($callback)) {
                $this->registerListener($classname, $callback);
            }
        }
    }

    public function getListenersForEvent(object $event): iterable
    {
        return $this->listeners[$event::class] ?? [];
    }
}
