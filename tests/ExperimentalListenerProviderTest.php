<?php
declare(strict_types=1);

namespace Konecnyjakub\EventDispatcher;

use Konecnyjakub\EventDispatcher\Events\Event;
use MyTester\Attributes\TestSuite;
use MyTester\TestCase;

#[TestSuite("ExperimentalListenerProvider")]
final class ExperimentalListenerProviderTest extends TestCase
{
    public function testGetListenersForEvent(): void
    {
        $listenerProvider = new ExperimentalListenerProvider();
        $this->assertSame([], iterator_to_array($listenerProvider->getListenersForEvent(new Event())));
        $this->assertSame([], iterator_to_array($listenerProvider->getListenersForEvent(new \stdClass())));

        $closure = function (Event $event) {
        };
        $invokableListener = new InvokableListener();
        $object = new class
        {
            #[Listener(priority: ExperimentalListenerProvider::PRIORITY_HIGH)]
            public function listener(Event $event): void
            {
            }
        };
        $arrayListener = [$object, "listener", ];

        $listenerProvider = new ExperimentalListenerProvider();
        $listenerProvider->addListener($closure);
        $listenerProvider->addListener($invokableListener);
        $this->assertSame(
            [$invokableListener, $closure, ],
            iterator_to_array($listenerProvider->getListenersForEvent(new Event()))
        );
        $this->assertSame([], iterator_to_array($listenerProvider->getListenersForEvent(new \stdClass())));

        $listenerProvider = new ExperimentalListenerProvider();
        $listenerProvider->addListeners([$closure, $invokableListener, ]);
        $listenerProvider->addListener($arrayListener);
        $this->assertSame(
            [$arrayListener, $invokableListener, $closure, ],
            iterator_to_array($listenerProvider->getListenersForEvent(new Event()))
        );
        $this->assertSame([], iterator_to_array($listenerProvider->getListenersForEvent(new \stdClass())));

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
        $listenerProvider = new ExperimentalListenerProvider();
        $listenerProvider->addSubscriber($eventSubscriber);
        $this->assertSame(
            [[$eventSubscriber, "two"], [$eventSubscriber, "one"], ],
            iterator_to_array($listenerProvider->getListenersForEvent(new Event()))
        );
        $this->assertSame([], iterator_to_array($listenerProvider->getListenersForEvent(new \stdClass())));
    }

    public function testInvalidCallbacks(): void
    {
        $this->assertThrowsException(function () {
            $listenerProvider = new ExperimentalListenerProvider();
            $listenerProvider->addListener(function (Event $event, int $number) {
            });
        }, InvalidListenerException::class, "The callback has to accept exactly 1 parameter");

        $this->assertThrowsException(function () {
            $listenerProvider = new ExperimentalListenerProvider();
            $listenerProvider->addListener(function (int $number) {
            });
        }, InvalidListenerException::class, "The callback's first parameter has to be a class name");
    }
}
