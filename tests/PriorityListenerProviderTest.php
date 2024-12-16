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
        $this->assertSame([], $listenerProvider->getListenersForEvent(new Event()));
        $this->assertSame([], $listenerProvider->getListenersForEvent(new \stdClass()));

        $listenerProvider = new PriorityListenerProvider();
        $listenerProvider->registerListener(Event::class, "time", 0);
        $listenerProvider->registerListener(Event::class, "pi", 1);
        $this->assertSame(["pi", "time", ], $listenerProvider->getListenersForEvent(new Event()));
        $this->assertSame([], $listenerProvider->getListenersForEvent(new \stdClass()));

        $listenerProvider = new PriorityListenerProvider();
        $listenerProvider->addListeners(Event::class, ["pi", "time", ], 0);
        $listenerProvider->registerListener(Event::class, "getdate", 1);
        $this->assertSame(["getdate", "pi", "time", ], $listenerProvider->getListenersForEvent(new Event()));
        $this->assertSame([], $listenerProvider->getListenersForEvent(new \stdClass()));

        $eventSubscriber = new class implements IEventSubscriber
        {
            public function one(): void
            {
            }

            public function two(): void
            {
            }

            public static function getSubscribedEvents(): iterable
            {
                return [
                    Event::class => [
                        ["one", ], ["two", 1, ],
                    ]
                ];
            }
        };
        $listenerProvider = new PriorityListenerProvider();
        $listenerProvider->addSubscriber($eventSubscriber);
        $this->assertSame(
            [[$eventSubscriber, "two"], [$eventSubscriber, "one"], ],
            $listenerProvider->getListenersForEvent(new Event())
        );
        $this->assertSame([], $listenerProvider->getListenersForEvent(new \stdClass()));
    }
}
