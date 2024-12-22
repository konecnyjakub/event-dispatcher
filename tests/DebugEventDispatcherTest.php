<?php
declare(strict_types=1);

namespace Konecnyjakub\EventDispatcher;

use Konecnyjakub\EventDispatcher\Events\Event;
use Konecnyjakub\EventDispatcher\Events\TestStoppableEvent;
use MyTester\Attributes\TestSuite;
use MyTester\TestCase;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

#[TestSuite("DebugEventDispatcher")]
final class DebugEventDispatcherTest extends TestCase
{
    public function testDispatch(): void
    {
        $event = new Event();
        $var = 0;
        $listenerProvider = new PriorityListenerProvider();
        $listenerProvider->addListener($event::class, function () use (&$var) {
            $var++;
        });
        $logger = new class extends AbstractLogger
        {
            public array $records = [];

            public function log($level, \Stringable|string $message, array $context = []): void
            {
                $this->records[] = [
                    "message" => $message,
                    "type" => $context["type"],
                    "event" => $context["event"],
                ];
            }
        };
        $eventDispatcher = new DebugEventDispatcher(new EventDispatcher($listenerProvider), $logger);
        $this->assertSame($event, $eventDispatcher->dispatch($event));
        $this->assertSame(1, $var);
        $this->assertCount(1, $logger->records);
        $this->assertSame([
            "message" => "Dispatched event",
            "type" => $event::class,
            "event" => $event,
        ], $logger->records[0]);
    }
}
