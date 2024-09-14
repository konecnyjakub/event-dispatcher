<?php
declare(strict_types=1);

namespace Konecnyjakub\EventDispatcher;

use Konecnyjakub\EventDispatcher\Events\Event;
use MyTester\Attributes\TestSuite;
use MyTester\TestCase;
use Psr\EventDispatcher\ListenerProviderInterface;

#[TestSuite("ChainListenerProvider")]
final class ChainListenerProviderTest extends TestCase
{
    public function testGetListenersForEvent(): void
    {
        $listenerProvider = new ChainListenerProvider();
        $this->assertSame([], $listenerProvider->getListenersForEvent(new Event()));
        $this->assertSame([], $listenerProvider->getListenersForEvent(new \stdClass()));

        $listenerProvider = new ChainListenerProvider();
        $provider1 = new class implements ListenerProviderInterface
        {
            public function getListenersForEvent(object $event): iterable
            {
                return match ($event::class) {
                    Event::class => ["time", ],
                    default => [],
                };
            }
        };
        $listenerProvider->registerProvider($provider1);
        $provider2 = new class implements ListenerProviderInterface
        {
            public function getListenersForEvent(object $event): iterable
            {
                return match ($event::class) {
                    Event::class => ["pi", ],
                    default => [],
                };
            }
        };
        $listenerProvider->registerProvider($provider2);
        $this->assertSame(["time", "pi", ], $listenerProvider->getListenersForEvent(new Event()));
        $this->assertSame([], $listenerProvider->getListenersForEvent(new \stdClass()));
    }
}
