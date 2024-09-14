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

    public function getListenersForEvent(object $event): iterable
    {
        return $this->listeners[$event::class] ?? [];
    }
}
