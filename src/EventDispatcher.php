<?php
declare(strict_types=1);

namespace Konecnyjakub\EventDispatcher;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\StoppableEventInterface;
use Psr\Log\LoggerInterface;

final readonly class EventDispatcher implements EventDispatcherInterface
{
    public function __construct(
        private ListenerProviderInterface $listenerProvider,
        private ?LoggerInterface $logger = null
    ) {
    }

    /**
     * @template T of object
     * @param T $event
     * @return T
     */
    public function dispatch(object $event): object
    {
        $this->logger?->debug("Dispatched event", ["type" => $event::class, "event" => $event, ]);
        /** @var callable $listener */
        foreach ($this->listenerProvider->getListenersForEvent($event) as $listener) {
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                break;
            }
            $listener($event);
        }
        return $event;
    }
}
