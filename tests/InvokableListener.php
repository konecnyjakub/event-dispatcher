<?php
declare(strict_types=1);

namespace Konecnyjakub\EventDispatcher;

use Konecnyjakub\EventDispatcher\Events\Event;

#[Listener(priority: 1)]
final class InvokableListener
{
    public function __invoke(Event $event): void
    {
    }
}
