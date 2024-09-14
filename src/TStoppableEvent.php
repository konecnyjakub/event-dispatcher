<?php
declare(strict_types=1);

namespace Konecnyjakub\EventDispatcher;

trait TStoppableEvent
{
    private bool $stopped = false;

    public function isPropagationStopped(): bool
    {
        return $this->stopped;
    }

    public function stopPropagation(): void
    {
        $this->stopped = true;
    }
}
