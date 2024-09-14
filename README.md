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
