<?php
declare(strict_types=1);

namespace Konecnyjakub\EventDispatcher;

use Psr\EventDispatcher\EventDispatcherInterface;

final readonly class DummyEventDispatcher implements EventDispatcherInterface
{
    /**
     * @template T of object
     * @param T $event
     * @return T
     */
    public function dispatch(object $event): object
    {
        return $event;
    }
}
