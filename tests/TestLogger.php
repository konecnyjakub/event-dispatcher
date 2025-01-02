<?php
declare(strict_types=1);

namespace Konecnyjakub\EventDispatcher;

use Psr\Log\AbstractLogger;

final class TestLogger extends AbstractLogger
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
}
