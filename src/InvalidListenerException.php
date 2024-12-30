<?php
declare(strict_types=1);

namespace Konecnyjakub\EventDispatcher;

use DomainException;

/**
 * Exception thrown if a callback is not a valid event listener
 */
class InvalidListenerException extends DomainException
{
}
