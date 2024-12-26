<?php
declare(strict_types=1);

namespace Konecnyjakub\EventDispatcher;

use Konecnyjakub\EventDispatcher\Events\Event;
use MyTester\Attributes\TestSuite;
use MyTester\TestCase;

#[TestSuite("PriorityListenerProvider")]
final class PriorityListenerProviderTest extends TestCase
{
    public function testGetListenersForEvent(): void
    {
        $listenerProvider = new PriorityListenerProvider();
        $this->assertSame([], iterator_to_array($listenerProvider->getListenersForEvent(new Event())));
        $this->assertSame([], iterator_to_array($listenerProvider->getListenersForEvent(new \stdClass())));

        $listenerProvider = new PriorityListenerProvider();
        $listenerProvider->registerListener(Event::class, "time", 0);
        $listenerProvider->registerListener(Event::class, "pi", 1);
        $this->assertSame(["pi", "time", ], iterator_to_array($listenerProvider->getListenersForEvent(new Event())));
        $this->assertSame([], iterator_to_array($listenerProvider->getListenersForEvent(new \stdClass())));

        $listenerProvider = new PriorityListenerProvider();
        $listenerProvider->addListeners(Event::class, ["pi", "time", ], 0);
        $listenerProvider->registerListener(Event::class, "getdate", 1);
        $this->assertSame(
            ["getdate", "pi", "time", ],
            iterator_to_array($listenerProvider->getListenersForEvent(new Event()))
        );
        $this->assertSame([], iterator_to_array($listenerProvider->getListenersForEvent(new \stdClass())));

        $eventSubscriber = new TestEventSubscriber();
        $listenerProvider = new PriorityListenerProvider();
        $listenerProvider->addSubscriber($eventSubscriber);
        $this->assertSame(
            [[$eventSubscriber, "two"], [$eventSubscriber, "one"], [$eventSubscriber, "three"], ],
            iterator_to_array($listenerProvider->getListenersForEvent(new Event()))
        );
        $this->assertSame([], iterator_to_array($listenerProvider->getListenersForEvent(new \stdClass())));
    }
}
