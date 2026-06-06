<?php
declare(strict_types=1);

namespace Konecnyjakub\EventDispatcher;

use Konecnyjakub\Container\SimpleContainer;
use Konecnyjakub\EventDispatcher\Events\AbstractEvent;
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

        $closure = static function (Event $event): void {
        };
        $closureAbstract = static function (AbstractEvent $event): void {
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

        $listenerProvider = new AutoListenerProvider();
        $listenerProvider->addListeners($eventSubscriber);
        $this->assertSame(
            [[$eventSubscriber, "three"], ],
            iterator_to_array($listenerProvider->getListenersForEvent(new Event()))
        );
        $this->assertSame([], iterator_to_array($listenerProvider->getListenersForEvent(new \stdClass())));

        $container = new SimpleContainer();
        $container->set("service1", $invokableListener);
        $container->set("service2", $closure);
        $listenerProvider = new AutoListenerProvider(container: $container);
        $listenerProvider->addServiceListener("service2");
        $listenerProvider->addServiceListener("service1");
        $this->assertSame(
            [$invokableListener, $closure, ],
            iterator_to_array($listenerProvider->getListenersForEvent(new Event()))
        );
        $this->assertSame([], iterator_to_array($listenerProvider->getListenersForEvent(new \stdClass())));

        $listenerProvider = new AutoListenerProvider();
        $listenerProvider->addListener($closure);
        $listenerProvider->addListener($closureAbstract);
        $this->assertSame(
            [$closure, $closureAbstract, ],
            iterator_to_array($listenerProvider->getListenersForEvent(new Event()))
        );
        $this->assertSame(
            [$closureAbstract, ],
            iterator_to_array($listenerProvider->getListenersForEvent(new class extends AbstractEvent {
            }))
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
        }, InvalidListenerException::class, "The callback's return type has to be explicitly set to void");
        $this->assertThrowsException(function () {
            $listenerProvider = new AutoListenerProvider();
            $listenerProvider->addListener(function (Event $event): null {
                return null;
            });
        }, InvalidListenerException::class, "The callback's return type has to be explicitly set to void");
        $this->assertThrowsException(function () {
            $listenerProvider = new AutoListenerProvider();
            $listenerProvider->addServiceListener("test");
        }, ContainerNotSetException::class);
        $this->assertThrowsException(function () {
            $container = new SimpleContainer();
            $listenerProvider = new AutoListenerProvider(container: $container);
            $listenerProvider->addServiceListener("test");
        }, InvalidListenerException::class, "The container does not have service 'test'");
        $this->assertThrowsException(function () {
            $container = new SimpleContainer();
            $container->set("test", "abc");
            $listenerProvider = new AutoListenerProvider(container: $container);
            $listenerProvider->addServiceListener("test");
        }, InvalidListenerException::class, "Service 'test' is not an object");
        $this->assertThrowsException(function () {
            $container = new SimpleContainer();
            $container->set("test", new class {
                public function method(Event $event): string
                {
                    return "";
                }
            });
            $listenerProvider = new AutoListenerProvider(container: $container);
            $listenerProvider->addServiceListener("test", "method");
        }, InvalidListenerException::class, "The callback's return type has to be explicitly set to void");
    }
}
