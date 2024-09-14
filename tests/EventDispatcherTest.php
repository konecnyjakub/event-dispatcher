<?php
declare(strict_types=1);

namespace Konecnyjakub\EventDispatcher;

use Konecnyjakub\EventDispatcher\Events\Event;
use Konecnyjakub\EventDispatcher\Events\TestStoppableEvent;
use MyTester\Attributes\TestSuite;
use MyTester\TestCase;

#[TestSuite("EventDispatcher")]
final class EventDispatcherTest extends TestCase
{
    public function testDispatch(): void
    {
        $event = new Event();
        $var = 0;
        $listenerProvider = new ListenerProvider();
        $listenerProvider->registerListener($event::class, function () use (&$var) {
            $var++;
        });
        $eventDispatcher = new EventDispatcher($listenerProvider);
        $this->assertSame($event, $eventDispatcher->dispatch($event));
        $this->assertSame(1, $var);

        $event = new TestStoppableEvent();
        $var = 0;
        $listenerProvider = new ListenerProvider();
        $listenerProvider->registerListener($event::class, function (TestStoppableEvent $event) use (&$var) {
            $var++;
            $event->stopPropagation();
        });
        $listenerProvider->registerListener($event::class, function (TestStoppableEvent $event) use (&$var) {
            $var++;
        });
        $eventDispatcher = new EventDispatcher($listenerProvider);
        $this->assertSame($event, $eventDispatcher->dispatch($event));
        $this->assertSame(1, $var);
        $this->assertTrue($event->isPropagationStopped());
    }
}
