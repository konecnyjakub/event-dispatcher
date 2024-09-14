<?php
declare(strict_types=1);

namespace Konecnyjakub\EventDispatcher\Events;

use Konecnyjakub\EventDispatcher\TStoppableEvent;
use Psr\EventDispatcher\StoppableEventInterface;

final class TestStoppableEvent implements StoppableEventInterface
{
    use TStoppableEvent;
}
