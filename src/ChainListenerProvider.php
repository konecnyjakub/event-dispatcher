<?php
declare(strict_types=1);

namespace Konecnyjakub\EventDispatcher;

use Psr\EventDispatcher\ListenerProviderInterface;

final class ChainListenerProvider implements ListenerProviderInterface
{
    /** @var ListenerProviderInterface[] */
    private array $providers = [];

    public function addProvider(ListenerProviderInterface $listenerProvider): void
    {
        $this->providers[] = $listenerProvider;
    }

    /**
     * @return callable[]
     */
    public function getListenersForEvent(object $event): iterable
    {
        $listeners = [];
        foreach ($this->providers as $listenerProvider) {
            $listeners = array_merge($listeners, iterator_to_array($listenerProvider->getListenersForEvent($event)));
        }
        /** @var callable[] $listeners */
        return $listeners;
    }
}
