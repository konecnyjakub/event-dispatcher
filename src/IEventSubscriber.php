<?php
declare(strict_types=1);

namespace Konecnyjakub\EventDispatcher;

interface IEventSubscriber
{
    public static function getSubscribedEvents(): iterable;
}
