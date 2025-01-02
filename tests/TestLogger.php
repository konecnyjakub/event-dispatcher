<?php
declare(strict_types=1);

namespace Konecnyjakub\EventDispatcher;

use Psr\Log\AbstractLogger;

final class TestLogger extends AbstractLogger
{
    /**
     * @var array{message: string, type: class-string, event: object}[]
     */
    public array $records = [];

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        $this->records[] = [ // @phpstan-ignore assign.propertyType
            "message" => (string) $message,
            "type" => $context["type"],
            "event" => $context["event"],
        ];
    }
}
