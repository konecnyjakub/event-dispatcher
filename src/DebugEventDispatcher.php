<?php
declare(strict_types=1);

namespace Konecnyjakub\EventDispatcher;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

final class DebugEventDispatcher implements EventDispatcherInterface
{
    /** @var array<class-string, int> */
    private array $dispatchedEvents = [];

    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly LoggerInterface $logger
    ) {
    }

    public function dispatch(object $event): object
    {
        $this->logger->debug("Dispatched event", ["type" => $event::class, "event" => $event, ]);
        if (!array_key_exists($event::class, $this->dispatchedEvents)) {
            $this->dispatchedEvents[$event::class] = 0;
        }
        $this->dispatchedEvents[$event::class]++;
        return $this->dispatcher->dispatch($event);
    }

    /**
     * @param class-string $event
     */
    public function dispatched(string $event, int $atLeastTimes = 1): bool
    {
        $times = $this->dispatchedEvents[$event] ?? 0;
        return $times >= $atLeastTimes;
    }
}
