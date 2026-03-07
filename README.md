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

use Konecnyjakub\EventDispatcher\AutoListenerProvider;
use Konecnyjakub\EventDispatcher\EventDispatcher;

class MyEvent {

}

$listenerProvider = new AutoListenerProvider();
$listenerProvider->addListener(function (MyEvent $event): void {
    echo "Event triggered\n";
});
$eventDispatcher = new EventDispatcher($listenerProvider);
$eventDispatcher->dispatch(new MyEvent());
```

AutoListenerProvider is a smart listener provider, its method addListener (and others, see below) automatically detects which type of event the added listener is for from the (first) parameter it accepts. But for now, it only accepts objects of that class, not any subclasses. This is the simplest possible use case, AutoListenerProvider has more features, see below.

Advanced usage
--------------

### What can be registered as listener

Anything that is a callable. You can pass an anonymous function, a function name as string, an object with the __invoke method, an array with an object at index 0 and a method name at index 1, static class method as string. See PHP documentation on [callables](https://www.php.net/manual/en/language.types.callable.php), if it is described on that page, it can be used as listener.

### Metadata for listeners

When registering a listener, AutoListenerProvider automatically looks for its metadata. You can add metadata to a listener via attribute Konecnyjakub\EventDispatcher\Listener.

Currently only one feature is implemented via metadata: priority. It is described in detail later in this document.

### Registering multiple listeners at once

It is possible to register multiple listeners at the same time in AutoListenerProvider, just pass an array/iterable of arrays into method addListeners.

```php
<?php
declare(strict_types=1);

use Konecnyjakub\EventDispatcher\AutoListenerProvider;
use Konecnyjakub\EventDispatcher\EventDispatcher;

class MyEvent {

}

$callback1 = function (MyEvent $event){
};
$callback2 = function (MyEvent $event){
};

$listenerProvider = new AutoListenerProvider();
$listenerProvider->addListeners([$callback1, $callback2, ]);
$eventDispatcher = new EventDispatcher($listenerProvider);
$eventDispatcher->dispatch(new MyEvent());
```

It is also possible to pass an object to method addListeners. That automatically adds all public methods on the object with attribute Konecnyjakub\EventDispatcher\Listener as listeners. Example:

```php
declare(strict_types=1);

use Konecnyjakub\EventDispatcher\AutoListenerProvider;
use Konecnyjakub\EventDispatcher\EventDispatcher;
use Konecnyjakub\EventDispatcher\Listener;

class MyEvent {

}

$object = new class {
    public function one(Event $event): void
    {
    }

    public function two(Event $event): void
    {
    }

    #[Listener]
    public function three(Event $event): void
    {
    }
}

$listenerProvider = new AutoListenerProvider();
$listenerProvider->addListeners($object);
$eventDispatcher = new EventDispatcher($listenerProvider);
$eventDispatcher->dispatch(new MyEvent());
```

In this example method three is registered as listener.

### Priority for listeners

The default listener provider supports setting priority for listeners, listeners with higher priority are triggered before those with lower priority. It is set with attribute Konecnyjakub\EventDispatcher\Listener. Example:

```php
<?php
declare(strict_types=1);

use Konecnyjakub\EventDispatcher\AutoListenerProvider;
use Konecnyjakub\EventDispatcher\EventDispatcher;
use Konecnyjakub\EventDispatcher\Listener;

class MyEvent {

}

function one (MyEvent $event){
};
#[Listener(priority: 1)]
function two (MyEvent $event){
};

$listenerProvider = new AutoListenerProvider();
$listenerProvider->addListener("one");
$listenerProvider->addListener("two");
$eventDispatcher = new EventDispatcher($listenerProvider);
$eventDispatcher->dispatch(new MyEvent());
```

In the example, function two is called before function one.

Setting priority is supported also with method addListeners, in that case priority is determined separately for each listener.

The listener provider provides constants  PRIORITY_HIGH, PRIORITY_NORMAL and PRIORITY_LOW that can be used for setting priority. PRIORITY_NORMAL is assumed if not specified.

### Multiple listener providers

If you need to use multiple listener providers at the same time, just use ChainListenerProvider.

```php
<?php
declare(strict_types=1);

use Konecnyjakub\EventDispatcher\AutoListenerProvider;
use Konecnyjakub\EventDispatcher\ChainListenerProvider;
use Konecnyjakub\EventDispatcher\EventDispatcher;

class MyEvent {

}

$listenerProvider = new ChainListenerProvider();
$provider1 = new AutoListenerProvider();
$provider1->addListener(function (MyEvent $event): void {
});
$provider2 = new AutoListenerProvider();
$listenerProvider->addListener(function (MyEvent $event): void {
});
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

use Konecnyjakub\EventDispatcher\AutoListenerProvider;
use Konecnyjakub\EventDispatcher\EventDispatcher;
use Konecnyjakub\EventDispatcher\Listener;
use Konecnyjakub\EventDispatcher\TStoppableEvent;

class MyEvent {
    use TStoppableEvent;
}

function one (MyEvent $event){
};
#[Listener(priority: 1)]
function two (MyEvent $event){
    echo "Event triggered\n";
    $event->stopPropagation();
};

$listenerProvider = new AutoListenerProvider();
$listenerProvider->addListener("one");
$listenerProvider->addListener("two");
$eventDispatcher = new EventDispatcher($listenerProvider);
$eventDispatcher->dispatch(new MyEvent());
```

In this example only function two is run (because it has higher priority and stops the event).

### Event subscribers

An alternative way to register listeners is to use event subscribers. An event subscriber is an object which names methods from the same class that listen to a named event. They have to implement the Konecnyjakub\EventDispatcher\IEventSubscriber interface and are added to AutoListenerProvider via method addSubscriber.

The method getSubscribedEvents has to return an array or a traversable object in which the key is a class name (the event's name) and the value is an array of listeners. Each listener is again an array where first value is name of a method of the same class and second value can be a priority for that listener. Priority specified this way overrides priority set by the attribute.


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
$eventDispatcher = new EventDispatcher($listenerProvider);
$eventDispatcher->dispatch(new MyEvent());
```

In this example, method three is called first, method two second and method one last.

### Container services as listeners

It is possible to register services from a [PSR-11](https://www.php-fig.org/psr/psr-11/) container as listeners. You have to pass a container to AutoListenerProvider's container and then you can use method addServiceListener. The method addServiceListener takes name of the service as first parameter, you can pass a method name as second parameter (default value is __invoke). The service has to be an object.

```php
<?php
declare(strict_types=1);

use Konecnyjakub\EventDispatcher\AutoListenerProvider;
use Konecnyjakub\EventDispatcher\EventDispatcher;
use Konecnyjakub\EventDispatcher\Listener;
use Psr\Container\ContainerInterface;

class MyEvent {

}

#[Listener(priority: 1)]
final class ServiceOne {
    public function __invoke(MyEvent $event): void {
    }
}

final class ServiceTwo {
    public function method(MyEvent $event): void {
    }
}

$container = new class implements ContainerInterface{
    /** @var array<string, mixed> */
    private array $services = [];

    public function get(string $id): mixed
    {
        if (!$this->has($id)) {
            throw new class extends RuntimeException implements NotFoundExceptionInterface
            {
            };
        }
        return $this->services[$id];
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->services);
    }

    public function set(string $id, mixed $service): void
    {
        $this->services[$id] = $service;
    }
};
$container->set("one", new ServiceOne());
$container->set("two", new ServiceTwo());

$listenerProvider = new AutoListenerProvider(container: $container);
$listenerProvider->addServiceListener("two", "method");
$listenerProvider->addServiceListener("one");
$eventDispatcher = new EventDispatcher($listenerProvider);
$eventDispatcher->dispatch(new MyEvent());
```

In this example, method ServiceOne::__invoke is called first, method ServiceTwo::method second.

### Debugging dispatched events

If you want to debug dispatched events, you can use included DebugEventDispatcher. Its constructor takes an event dispatcher (to which dispatching events is delegated) and a [PSR-3](https://www.php-fig.org/psr/psr-3/) logger which is used to log relevant info.

Currently it only logs that an event was dispatched.

It can also tell you if an event of a certain type was dispatched, just use method dispatched with a class name. You can also specify with second optional parameter at least how many times it should have been dispatched.

```php
<?php
declare(strict_types=1);

use Konecnyjakub\EventDispatcher\AutoListenerProvider;
use Konecnyjakub\EventDispatcher\DebugEventDispatcher;
use Konecnyjakub\EventDispatcher\EventDispatcher;
use Psr\Log\AbstractLogger;

class MyEvent {

}

$callback1 = function (MyEvent $event){
};
$callback2 = function (MyEvent $event){
};

$listenerProvider = new AutoListenerProvider();
$listenerProvider->addListeners([$callback1, $callback2, ]);
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

Alternatively, if you only want to log dispatched events, you can just pass a PSR-3 logger to EventDispatcher's constructor. If it is set, the dispatch method will automatically use it to log.

### Simple event dispatcher

If you are in a situation where you need to pass an event dispatcher somewhere but want it to do absolutely nothing (e.g. in tests), you can use DummyEventDispatcher. It does not do anything with the passed event, just return it like the psr requires. An example:

```php
<?php
declare(strict_types=1);

use Konecnyjakub\EventDispatcher\DummyEventDispatcher;

class MyEvent {

}

$eventDispatcher = new DummyEventDispatcher();
$eventDispatcher->dispatch(new MyEvent()); // nothing happens
```
