Event Dispatcher
================

[![Total Downloads](https://poser.pugx.org/konecnyjakub/event-dispatcher/downloads)](https://packagist.org/packages/konecnyjakub/event-dispatcher) [![Latest Stable Version](https://poser.pugx.org/konecnyjakub/event-dispatcher/v/stable)](https://gitlab.com/konecnyjakub/event-dispatcher/-/releases) [![build status](https://gitlab.com/konecnyjakub/event-dispatcher/badges/master/pipeline.svg?ignore_skipped=true)](https://gitlab.com/konecnyjakub/event-dispatcher/-/commits/master) [![coverage report](https://gitlab.com/konecnyjakub/event-dispatcher/badges/master/coverage.svg)](https://gitlab.com/konecnyjakub/event-dispatcher/-/commits/master) [![License](https://poser.pugx.org/konecnyjakub/event-dispatcher/license)](https://gitlab.com/konecnyjakub/event-dispatcher/-/blob/master/LICENSE.md)

A simple PSR-14 event dispatcher

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
$listenerProvider->registerListener(new MyEvent(), function (MyEvent $event) {
    echo "Event triggered\n";
});
$eventDispatcher = new EventDispatcher($listenerProvider);
$eventDispatcher->dispatch(new MyEvent());

```
