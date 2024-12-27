Event Dispatcher
================

[![Total Downloads](https://poser.pugx.org/konecnyjakub/event-dispatcher/downloads)](https://packagist.org/packages/konecnyjakub/event-dispatcher) [![Latest Stable Version](https://poser.pugx.org/konecnyjakub/event-dispatcher/v/stable)](https://gitlab.com/konecnyjakub/event-dispatcher/-/releases) [![build status](https://gitlab.com/konecnyjakub/event-dispatcher/badges/master/pipeline.svg?ignore_skipped=true)](https://gitlab.com/konecnyjakub/event-dispatcher/-/commits/master) [![coverage report](https://gitlab.com/konecnyjakub/event-dispatcher/badges/master/coverage.svg)](https://gitlab.com/konecnyjakub/event-dispatcher/-/commits/master) [![License](https://poser.pugx.org/konecnyjakub/event-dispatcher/license)](https://gitlab.com/konecnyjakub/event-dispatcher/-/blob/master/LICENSE.md)

This is a simple [PSR-14](https://www.php-fig.org/psr/psr-14/) event dispatcher, it allows registering callbacks as event listeners. It also supports stoppable events from psr and allows setting priority for listeners, using event subscribers or using multiple listener providers at the same time.

Installation
------------

The best way to install Event Dispatcher is via Composer. Just add konecnyjakub/event-dispatcher to your dependencies.

Quick start
-----------

```php
<?php
declare(strict_types=1);

use Konecnyjakub\EventDispatcher\EventDispatcher;
use Konecnyjakub\EventDispatcher\PriorityListenerProvider;

class MyEvent {

}

$listenerProvider = new PriorityListenerProvider();
$listenerProvider->addListener(MyEvent::class, function (MyEvent $event) {
    echo "Event triggered\n";
});
$eventDispatcher = new EventDispatcher($listenerProvider);
$eventDispatcher->dispatch(new MyEvent());
```

Advanced usage
--------------

### Registering multiple listeners at once

It is possible to register multiple listeners at the same time in PriorityListenerProvider, just pass an array/iterable of arrays into method addListeners.

```php
<?php
declare(strict_types=1);

use Konecnyjakub\EventDispatcher\EventDispatcher;
use Konecnyjakub\EventDispatcher\PriorityListenerProvider;

class MyEvent {

}

$listenerProvider = new PriorityListenerProvider();
$listenerProvider->addListeners(MyEvent::class, ["time", "pi", ]);
$eventDispatcher = new EventDispatcher($listenerProvider);
$eventDispatcher->dispatch(new MyEvent());
```

### Priority for listeners

The default listener provider supports setting priority for listeners, listeners with higher priority are triggered before those with lower priority. Example:

```php
<?php
declare(strict_types=1);

use Konecnyjakub\EventDispatcher\EventDispatcher;
use Konecnyjakub\EventDispatcher\PriorityListenerProvider;

class MyEvent {

}

$listenerProvider = new PriorityListenerProvider();
$listenerProvider->addListener(MyEvent::class, "time", 0);
$listenerProvider->addListener(MyEvent::class, "pi", 1);
$eventDispatcher = new EventDispatcher($listenerProvider);
$eventDispatcher->dispatch(new MyEvent());
```

In the example, function pi is called before function time.

It also possible to register multiple listeners with the same priority at the same time, just use method addListeners.

```php
<?php
declare(strict_types=1);

use Konecnyjakub\EventDispatcher\EventDispatcher;
use Konecnyjakub\EventDispatcher\PriorityListenerProvider;

class MyEvent {

}

$listenerProvider = new PriorityListenerProvider();
$listenerProvider->addListeners(Event::class, ["pi", "time", ], 0);
$listenerProvider->addListener(Event::class, "getdate", 1);
$eventDispatcher = new EventDispatcher($listenerProvider);
$eventDispatcher->dispatch(new MyEvent());
```

The listener provider provides constants  PRIORITY_HIGH, PRIORITY_NORMAL and PRIORITY_LOW that can be used for parameter priority of methods addListener/addListeners.

### Multiple listener providers

If you need to use multiple listener providers at the same time, just use ChainListenerProvider.

```php
<?php
declare(strict_types=1);

use Konecnyjakub\EventDispatcher\ChainListenerProvider;
use Konecnyjakub\EventDispatcher\EventDispatcher;
use Konecnyjakub\EventDispatcher\PriorityListenerProvider;

class MyEvent {

}

$listenerProvider = new ChainListenerProvider();
$provider1 = new PriorityListenerProvider();
$provider1->addListener(MyEvent::class, "time");
$provider2 = new PriorityListenerProvider();
$provider2->addListener(MyEvent::class, "pi");
$listenerProvider->addProvider($provider1);
$listenerProvider->addProvider($provider2);
$eventDispatcher = new EventDispatcher($listenerProvider);
$eventDispatcher->dispatch(new MyEvent());
```

### Stoppable events

The provided event dispatcher supports stoppable events (as defined in psr). We even provide trait TStoppableEvent which you can use in your event classes.

```php
<?php
declare(strict_types=1);

use Konecnyjakub\EventDispatcher\EventDispatcher;
use Konecnyjakub\EventDispatcher\PriorityListenerProvider;
use Konecnyjakub\EventDispatcher\TStoppableEvent;

class MyEvent {
    use TStoppableEvent;
}

$listenerProvider = new PriorityListenerProvider();
$listenerProvider->addListener(MyEvent::class, function (MyEvent $event) {
    echo "Event triggered\n";
    $event->stopPropagation();
});
$listenerProvider->addListener(MyEvent::class, "time");
$eventDispatcher = new EventDispatcher($listenerProvider);
$eventDispatcher->dispatch(new MyEvent());
```

### Event subscribers

An alternative way to register listeners, is to use event subscribers. An event subscriber is an object which names methods from the same class that listen to a named event. They have to implement the Konecnyjakub\EventDispatcher\IEventSubscriber interface and are added to ListenerProvider or PriorityListenerProvider via method addSubscriber.

The method getSubscribedEvents has to return an array or a traversable object in which the key is a class name (the event's name) and the value is an array of listeners. Each listener is again an array where first value is name of a method of the same class and second value can be a priority for that listener (it is of course taken into account only by PriorityListenerProvider).


```php
<?php
declare(strict_types=1);

use Konecnyjakub\EventDispatcher\EventDispatcher;
use Konecnyjakub\EventDispatcher\IEventSubscriber;
use Konecnyjakub\EventDispatcher\PriorityListenerProvider;

class MyEvent {

}

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

$listenerProvider = new PriorityListenerProvider();
$listenerProvider->addSubscriber($eventSubscriber);
$eventDispatcher = new EventDispatcher($listenerProvider);
$eventDispatcher->dispatch(new MyEvent());
```

In the example method two is called before method one.

### Easier registration of listeners

There is an experimental listener provider, AutoListenerProvider that makes registration of event listeners even easier. It has the same methods as PriorityListenerProvider (addListener, addListeners and addSubscriber) but you only have to pass the listener itself to methods addListener and addListeners, everything else (type of event it listens to and priority) is determined from the callback itself (its signature or a used attribute). That obviously requires the callback to have properly type hinted the parameter (and have void return type hinted as recommended by the psr). Other metadata (at the moment only priority) can be specified by attribute Konecnyjakub\EventDispatcher\Listener. Event subscribers are defined the same way as with PriorityListenerProvider but if priority is not specified in the result of method getSubscribedEvents, it is taken from the above mentioned attribute if present. Examples:

```php
<?php
declare(strict_types=1);

use Konecnyjakub\EventDispatcher\AutoListenerProvider;
use Konecnyjakub\EventDispatcher\EventDispatcher;
use Konecnyjakub\EventDispatcher\Listener;

class MyEvent {

}

#[Listener(priority: 1)]
final class InvokableListener
{
    public function __invoke(Event $event): void
    {
    }
}

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
$listenerProvider->addListener($arrayListener);
$eventDispatcher = new EventDispatcher($listenerProvider);
$eventDispatcher->dispatch(new MyEvent());
```

In this example, $arrayListener is called first, $invokableListener second and $closure last.

```php
<?php
declare(strict_types=1);

use Konecnyjakub\EventDispatcher\AutoListenerProvider;
use Konecnyjakub\EventDispatcher\EventDispatcher;
use Konecnyjakub\EventDispatcher\IEventSubscriber;
use Konecnyjakub\EventDispatcher\Listener;

class MyEvent {

}

final class EventSubscriber implements IEventSubscriber
{
    public function one(Event $event): void
    {
    }

    public function two(Event $event): void
    {
    }

    #[Listener(priority: 2)]
    public function three(Event $event): void
    {
    }

    public static function getSubscribedEvents(): iterable
    {
        return [
            Event::class => [
                ["one", ], ["two", 1, ], ["three", ],
            ]
        ];
    }
}

$listenerProvider = new AutoListenerProvider();
$listenerProvider->addSubscriber(new EventSubscriber());
$eventDispatcher->dispatch(new MyEvent());
```

In this example, method three is called first, method two second and method one last.

In the next major version, PriorityListenerProvider will be removed and this listener provider will become the default one.

### Debugging dispatched events

If you want to debug dispatched events, you can use included DebugEventDispatcher. Its constructor takes an event dispatcher (to which dispatching events is delegated) and a [PSR-3](https://www.php-fig.org/psr/psr-3/) logger which is used to log relevant info.

Currently it only logs that an event was dispatched.

It can also tell you if an event of a certain type of dispatched, just use method dispatched with a class name. You can also specify with second optional parameter how many times it should have been dispatched.

```php
<?php
declare(strict_types=1);

use Konecnyjakub\EventDispatcher\DebugEventDispatcher;
use Konecnyjakub\EventDispatcher\EventDispatcher;
use Konecnyjakub\EventDispatcher\PriorityListenerProvider;
use Psr\Log\NullLogger;

class MyEvent {

}

$listenerProvider = new PriorityListenerProvider();
$listenerProvider->addListeners(MyEvent::class, ["time", "pi", ]);
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
$eventDispatcher->dispatched(MyEvent::class); // false
count($logger->records); // 0

$eventDispatcher->dispatch(new MyEvent());
$eventDispatcher->dispatched(MyEvent::class); // true
$eventDispatcher->dispatched(MyEvent::class, 1); // true
$eventDispatcher->dispatched(MyEvent::class, 2); // false
count($logger->records); // 1
```
