<?php
declare(strict_types=1);

namespace Konecnyjakub\EventDispatcher;

use Konecnyjakub\EventDispatcher\Events\Event;
use Konecnyjakub\EventDispatcher\Events\TestStoppableEvent;
use MyTester\Attributes\TestSuite;
use MyTester\TestCase;

#[TestSuite("DebugEventDispatcher")]
final class DebugEventDispatcherTest extends TestCase
{
    public function testDispatch(): void
    {
        $event = new Event();
        $var = 0;
        $listenerProvider = new AutoListenerProvider();
        $listenerProvider->addListener(function (Event $event) use (&$var): void {
            $var++;
        });
        $logger = new TestLogger();
        $eventDispatcher = new DebugEventDispatcher(new EventDispatcher($listenerProvider), $logger);
        $this->assertFalse($eventDispatcher->dispatched($event::class));
        $this->assertFalse($eventDispatcher->dispatched(TestStoppableEvent::class));
        $this->assertSame($event, $eventDispatcher->dispatch($event));
        $this->assertSame(1, $var);
        $this->assertCount(1, $logger->records);
        $this->assertSame([
            "message" => "Dispatched event",
            "type" => $event::class,
            "event" => $event,
        ], $logger->records[0]);
        $this->assertTrue($eventDispatcher->dispatched($event::class));
        $this->assertFalse($eventDispatcher->dispatched($event::class, 2));
        $this->assertFalse($eventDispatcher->dispatched(TestStoppableEvent::class));
        $this->assertSame($event, $eventDispatcher->dispatch($event));
        $this->assertTrue($eventDispatcher->dispatched($event::class, 2));
        $this->assertFalse($eventDispatcher->dispatched(TestStoppableEvent::class, 2));
    }
}
