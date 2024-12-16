<?php
declare(strict_types=1);

namespace Konecnyjakub\EventDispatcher;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

final readonly class DebugEventDispatcher implements EventDispatcherInterface
{
    public function __construct(private EventDispatcherInterface $dispatcher, private LoggerInterface $logger)
    {
    }

    public function dispatch(object $event): object
    {
        $this->logger->debug("Dispatched event", ["type" => $event::class, "event" => $event, ]);
        return $this->dispatcher->dispatch($event);
    }
}
