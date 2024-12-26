<?php
declare(strict_types=1);

namespace Konecnyjakub\EventDispatcher;

use Attribute;

/**
 * Provides metadata for an event listener
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::TARGET_FUNCTION)]
final readonly class Listener
{
    public function __construct(public int $priority = AutoListenerProvider::PRIORITY_NORMAL)
    {
    }
}
