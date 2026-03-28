<?php
declare(strict_types=1);

namespace Konecnyjakub\EventDispatcher;

use DomainException;

/**
 * Exception thrown if a callback is not a valid event listener
 */
class InvalidListenerException extends DomainException
{
    final public const int CODE_WRONG_NUMBER_OF_PARAMETERS = 1;
    final public const int CODE_WRONG_PARAMETER_TYPE = 2;
    final public const int CODE_PARAMETER_NOT_EVENT = 3;
    final public const int CODE_WRONG_RETURN_TYPE = 4;
    final public const int CODE_SERVICE_NOT_IN_CONTAINER = 5;
    final public const int CODE_WRONG_SERVICE_TYPE = 6;
}
