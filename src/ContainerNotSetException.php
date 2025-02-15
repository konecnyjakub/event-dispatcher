<?php
declare(strict_types=1);

namespace Konecnyjakub\EventDispatcher;

/**
 * Exception thrown if container is required for an operation but was not set
 */
class ContainerNotSetException extends \RuntimeException
{
}
