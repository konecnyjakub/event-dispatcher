<?php
declare(strict_types=1);

namespace Konecnyjakub\EventDispatcher;

use Konecnyjakub\EventDispatcher\Events\Event;
use MyTester\Attributes\TestSuite;
use MyTester\TestCase;

#[TestSuite("DummyEventDispatcher")]
final class DummyEventDispatcherTest extends TestCase
{
    public function testDispatch(): void
    {
        $event = new Event();
        $eventDispatcher = new DummyEventDispatcher();
        $this->assertSame($event, $eventDispatcher->dispatch($event));
    }
}
