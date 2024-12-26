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
        $provider1 = new PriorityListenerProvider();
        $provider1->addListener(Event::class, "time");
        $listenerProvider->registerProvider($provider1);
        $provider2 = new PriorityListenerProvider();
        $provider2->addListener(Event::class, "pi");
        $listenerProvider->registerProvider($provider2);
        $this->assertSame(["time", "pi", ], $listenerProvider->getListenersForEvent(new Event()));
        $this->assertSame([], $listenerProvider->getListenersForEvent(new \stdClass()));
    }
}
