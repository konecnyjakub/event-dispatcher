<?php
declare(strict_types=1);

namespace Konecnyjakub\EventDispatcher;

use Konecnyjakub\EventDispatcher\Events\Event;
use MyTester\Attributes\TestSuite;
use MyTester\TestCase;

#[TestSuite("ListenerProvider")]
final class ListenerProviderTest extends TestCase
{
    public function testGetListenersForEvent(): void
    {
        $listenerProvider = new ListenerProvider();
        $this->assertSame([], $listenerProvider->getListenersForEvent(new Event()));
        $this->assertSame([], $listenerProvider->getListenersForEvent(new \stdClass()));

        $listenerProvider = new ListenerProvider();
        $listenerProvider->registerListener(Event::class, "time");
        $this->assertSame(["time", ], $listenerProvider->getListenersForEvent(new Event()));
        $this->assertSame([], $listenerProvider->getListenersForEvent(new \stdClass()));

        $listenerProvider = new ListenerProvider();
        $listenerProvider->registerListeners(Event::class, ["time", "pi", ]);
        $this->assertSame(["time", "pi", ], $listenerProvider->getListenersForEvent(new Event()));
        $this->assertSame([], $listenerProvider->getListenersForEvent(new \stdClass()));
    }
}
