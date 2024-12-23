<?php
declare(strict_types=1);

namespace Konecnyjakub\EventDispatcher;

interface IEventSubscriber
{
    /**
     * @return iterable<class-string, array{0: string, 1?: int}[]>
     */
    public static function getSubscribedEvents(): iterable;
}
