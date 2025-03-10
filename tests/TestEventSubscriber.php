<?php
declare(strict_types=1);

namespace Konecnyjakub\EventDispatcher;

use Konecnyjakub\EventDispatcher\Events\Event;

final class TestEventSubscriber implements IEventSubscriber // @phpstan-ignore class.implementsDeprecatedInterface
{
    public function one(Event $event): void
    {
    }

    public function two(Event $event): void
    {
    }

    #[Listener(priority: 2)]
    public function three(Event $event): void
    {
    }

    public static function getSubscribedEvents(): iterable
    {
        return [
            Event::class => [
                ["one", ], ["two", 1, ], ["three", ],
            ]
        ];
    }
}
