<?php
declare(strict_types=1);

namespace Konecnyjakub\EventDispatcher;

use Konecnyjakub\EventDispatcher\Events\Event;
use MyTester\Attributes\TestSuite;
use MyTester\TestCase;

#[TestSuite("AutoListenerProvider")]
final class AutoListenerProviderTest extends TestCase
{
    public function testGetListenersForEvent(): void
    {
        $listenerProvider = new AutoListenerProvider();
        $this->assertSame([], iterator_to_array($listenerProvider->getListenersForEvent(new Event())));
        $this->assertSame([], iterator_to_array($listenerProvider->getListenersForEvent(new \stdClass())));

        $closure = function (Event $event): void {
        };
        $invokableListener = new InvokableListener();
        $object = new class
        {
            #[Listener(priority: AutoListenerProvider::PRIORITY_HIGH)]
            public function listener(Event $event): void
            {
            }
        };
        $arrayListener = [$object, "listener", ];

        $listenerProvider = new AutoListenerProvider();
        $listenerProvider->addListener($closure);
        $listenerProvider->addListener($invokableListener);
        $this->assertSame(
            [$invokableListener, $closure, ],
            iterator_to_array($listenerProvider->getListenersForEvent(new Event()))
        );
        $this->assertSame([], iterator_to_array($listenerProvider->getListenersForEvent(new \stdClass())));

        $listenerProvider = new AutoListenerProvider();
        $listenerProvider->addListeners([$closure, $invokableListener, ]);
        $listenerProvider->addListener($arrayListener);
        $this->assertSame(
            [$arrayListener, $invokableListener, $closure, ],
            iterator_to_array($listenerProvider->getListenersForEvent(new Event()))
        );
        $this->assertSame([], iterator_to_array($listenerProvider->getListenersForEvent(new \stdClass())));

        $eventSubscriber = new TestEventSubscriber();
        $listenerProvider = new AutoListenerProvider();
        $listenerProvider->addSubscriber($eventSubscriber);
        $this->assertSame(
            [[$eventSubscriber, "three"], [$eventSubscriber, "two"], [$eventSubscriber, "one"], ],
            iterator_to_array($listenerProvider->getListenersForEvent(new Event()))
        );
        $this->assertSame([], iterator_to_array($listenerProvider->getListenersForEvent(new \stdClass())));
    }

    public function testInvalidCallbacks(): void
    {
        $this->assertThrowsException(function () {
            $listenerProvider = new AutoListenerProvider();
            $listenerProvider->addListener(function (Event $event, int $number) {
            });
        }, InvalidListenerException::class, "The callback has to accept exactly 1 parameter");

        $this->assertThrowsException(function () {
            $listenerProvider = new AutoListenerProvider();
            $listenerProvider->addListener(function (int $number) {
            });
        }, InvalidListenerException::class, "The callback's first parameter has to be a class name");
        $this->assertThrowsException(function () {
            $listenerProvider = new AutoListenerProvider();
            $listenerProvider->addListener(function (Event $event) {
            });
        }, InvalidListenerException::class, "The callback's return type has to explicitly set to void");
        $this->assertThrowsException(function () {
            $listenerProvider = new AutoListenerProvider();
            $listenerProvider->addListener(function (Event $event): null {
                return null;
            });
        }, InvalidListenerException::class, "The callback's return type has to explicitly set to void");
    }
}
