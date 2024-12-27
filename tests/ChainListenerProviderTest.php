<?php
declare(strict_types=1);

namespace Konecnyjakub\EventDispatcher;

use Konecnyjakub\EventDispatcher\Events\Event;
use MyTester\Attributes\TestSuite;
use MyTester\TestCase;

#[TestSuite("ChainListenerProvider")]
final class ChainListenerProviderTest extends TestCase
{
    public function testGetListenersForEvent(): void
    {
        $listenerProvider = new ChainListenerProvider();
        $this->assertSame([], $listenerProvider->getListenersForEvent(new Event()));
        $this->assertSame([], $listenerProvider->getListenersForEvent(new \stdClass()));

        $listenerProvider = new ChainListenerProvider();
        $callback1 = function (Event $event): void {
        };
        $provider1 = new AutoListenerProvider();
        $provider1->addListener($callback1);
        $listenerProvider->addProvider($provider1);
        $provider2 = new AutoListenerProvider();
        $callback2 = function (Event $event): void {
        };
        $provider2->addListener($callback2);
        $listenerProvider->addProvider($provider2);
        $this->assertSame([$callback1, $callback2, ], $listenerProvider->getListenersForEvent(new Event()));
        $this->assertSame([], $listenerProvider->getListenersForEvent(new \stdClass()));
    }
}
