Event Dispatcher
================

[![Total Downloads](https://poser.pugx.org/konecnyjakub/event-dispatcher/downloads)](https://packagist.org/packages/konecnyjakub/event-dispatcher) [![Latest Stable Version](https://poser.pugx.org/konecnyjakub/event-dispatcher/v/stable)](https://gitlab.com/konecnyjakub/event-dispatcher/-/releases) [![build status](https://gitlab.com/konecnyjakub/event-dispatcher/badges/master/pipeline.svg?ignore_skipped=true)](https://gitlab.com/konecnyjakub/event-dispatcher/-/commits/master) [![coverage report](https://gitlab.com/konecnyjakub/event-dispatcher/badges/master/coverage.svg)](https://gitlab.com/konecnyjakub/event-dispatcher/-/commits/master) [![License](https://poser.pugx.org/konecnyjakub/event-dispatcher/license)](https://gitlab.com/konecnyjakub/event-dispatcher/-/blob/master/LICENSE.md)

This is a simple [PSR-14](https://www.php-fig.org/psr/psr-14/) event dispatcher, it allows registering callbacks as event listeners. It also supports stoppable events from psr and has listener providers that allow setting priority for listeners or using multiple listener providers at the same time.

Installation
------------

The best way to install Event Dispatcher is via Composer. Just add konecnyjakub/event-dispatcher to your dependencies.

Quick start
-----------

```php
<?php
declare(strict_types=1);

use Konecnyjakub\EventDispatcher\EventDispatcher;
use Konecnyjakub\EventDispatcher\ListenerProvider;

class MyEvent {

}

$listenerProvider = new ListenerProvider();
$listenerProvider->registerListener(MyEvent::class, function (MyEvent $event) {
    echo "Event triggered\n";
});
$eventDispatcher = new EventDispatcher($listenerProvider);
$eventDispatcher->dispatch(new MyEvent());
```

Advanced usage
--------------

### Registering multiple listeners at once

It is possible to register multiple listeners at the same time in ListenerProvider, just pass an array/iterable of arrays into method registerListeners.

```php
declare(strict_types=1);

use Konecnyjakub\EventDispatcher\EventDispatcher;
use Konecnyjakub\EventDispatcher\ListenerProvider;

class MyEvent {

}

$listenerProvider = new ListenerProvider();
$listenerProvider->registerListeners(MyEvent::class, ["time", "pi", ]);
$eventDispatcher = new EventDispatcher($listenerProvider);
$eventDispatcher->dispatch(new MyEvent());
```

### Priority for listeners

This library provides a listener provider that supports setting priority for listeners, listeners with higher priority are triggered before those with lower priority. Example:

```php
<?php
declare(strict_types=1);

use Konecnyjakub\EventDispatcher\EventDispatcher;
use Konecnyjakub\EventDispatcher\PriorityListenerProvider;

class MyEvent {

}

$listenerProvider = new PriorityListenerProvider();
$listenerProvider->registerListener(MyEvent::class, "time", 0);
$listenerProvider->registerListener(MyEvent::class, "pi", 1);
$eventDispatcher = new EventDispatcher($listenerProvider);
$eventDispatcher->dispatch(new MyEvent());
```

In the example, function pi is called before function time.

It also possible to register multiple listeners with the same priority at the same time, just use method registerListeners.

```php
<?php
declare(strict_types=1);

use Konecnyjakub\EventDispatcher\EventDispatcher;
use Konecnyjakub\EventDispatcher\PriorityListenerProvider;

class MyEvent {

}

$listenerProvider = new PriorityListenerProvider();
$listenerProvider->registerListeners(Event::class, ["pi", "time", ], 0);
$listenerProvider->registerListener(Event::class, "getdate", 1);
$eventDispatcher = new EventDispatcher($listenerProvider);
$eventDispatcher->dispatch(new MyEvent());
```

### Multiple listener providers

If you need to use multiple listener providers at the same time, just use ChainListenerProvider.

```php
<?php
declare(strict_types=1);

use Konecnyjakub\EventDispatcher\ChainListenerProvider;
use Konecnyjakub\EventDispatcher\EventDispatcher;
use Konecnyjakub\EventDispatcher\ListenerProvider;

class MyEvent {

}

$listenerProvider = new ChainListenerProvider();
$provider1 = new ListenerProvider();
$provider1->registerListener(MyEvent::class, "time");
$provider2 = new ListenerProvider();
$provider2->registerListener(MyEvent::class, "pi");
$listenerProvider->registerProvider($provider1);
$listenerProvider->registerProvider($provider2);
$eventDispatcher = new EventDispatcher($listenerProvider);
$eventDispatcher->dispatch(new MyEvent());
```

### Stoppable events

The provided event dispatcher supports stoppable events (as defined in psr). We even provide trait TStoppableEvent which you can use in your event classes.

```php
<?php
declare(strict_types=1);

use Konecnyjakub\EventDispatcher\EventDispatcher;
use Konecnyjakub\EventDispatcher\ListenerProvider;
use Konecnyjakub\EventDispatcher\TStoppableEvent;

class MyEvent {
    use TStoppableEvent;
}

$listenerProvider = new ListenerProvider();
$listenerProvider->registerListener(MyEvent::class, function (MyEvent $event) {
    echo "Event triggered\n";
    $event->stopPropagation();
});
$listenerProvider->registerListener(MyEvent::class, "time");
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

### Debugging dispatched events

If you want to debug dispatched events, you can use included DebugEventDispatcher. Its constructor takes an event dispatcher (to which dispatching events is delegated) and a [PSR-3](https://www.php-fig.org/psr/psr-3/) logger which is used to log relevant info.

Currently it only logs that an event was dispatched.

For an example of usage, see tests of the class.
