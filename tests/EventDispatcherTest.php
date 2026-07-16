<?php
declare(strict_types=1);

namespace Konecnyjakub\EventDispatcher;

use Konecnyjakub\EventDispatcher\Events\AbstractEvent;
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
        $listenerProvider = new AutoListenerProvider();
        $listenerProvider->addListener(static function (Event $event) use (&$var): void {
            $var++;
        });
        $eventDispatcher = new EventDispatcher($listenerProvider);
        $this->assertSame($event, $eventDispatcher->dispatch($event));
        $this->assertSame(1, $var);

        $event = new Event();
        $var = 0;
        $listenerProvider = new AutoListenerProvider();
        $listenerProvider->addListener(static function (Event $event) use (&$var): void {
            $var++;
        });
        $listenerProvider->addListener(static function (AbstractEvent $event) use (&$var): void {
            $var += 2;
        });
        $eventDispatcher = new EventDispatcher($listenerProvider);
        $this->assertSame($event, $eventDispatcher->dispatch($event));
        $this->assertSame(3, $var);

        $event = new TestStoppableEvent();
        $var = 0;
        $listenerProvider = new AutoListenerProvider();
        $listenerProvider->addListener(static function (TestStoppableEvent $event) use (&$var): void {
            $var++;
            $event->stopPropagation();
        });
        $listenerProvider->addListener(static function (TestStoppableEvent $event) use (&$var): void {
            $var++;
        });
        $eventDispatcher = new EventDispatcher($listenerProvider);
        $this->assertSame($event, $eventDispatcher->dispatch($event));
        $this->assertSame(1, $var);
        $this->assertTrue($event->isPropagationStopped());

        $event = new Event();
        $logger = new TestLogger();
        $listenerProvider = new AutoListenerProvider();
        $eventDispatcher = new EventDispatcher($listenerProvider, $logger);
        $eventDispatcher->dispatch($event);
        $this->assertCount(1, $logger->records);
        $this->assertSame([
            "message" => "Dispatched event",
            "type" => $event::class,
            "event" => $event,
        ], $logger->records[0]);
    }
}
