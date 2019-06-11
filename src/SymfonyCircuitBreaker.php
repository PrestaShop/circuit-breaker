<?php

namespace PrestaShop\CircuitBreaker;

use PrestaShop\CircuitBreaker\Contract\ClientInterface;
use PrestaShop\CircuitBreaker\Contract\SystemInterface;
use PrestaShop\CircuitBreaker\Contract\StorageInterface;
use PrestaShop\CircuitBreaker\Transition\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Symfony implementation of Circuit Breaker.
 */
final class SymfonyCircuitBreaker extends AdvancedCircuitBreaker
{
    /**
     * @var EventDispatcherInterface the Symfony Event Dispatcher
     */
    private $eventDispatcher;

    /**
     * @param SystemInterface $system
     * @param ClientInterface $client
     * @param StorageInterface $storage
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        SystemInterface $system,
        ClientInterface $client,
        StorageInterface $storage,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->eventDispatcher = $eventDispatcher;

        parent::__construct($system, $client, $storage, new EventDispatcher($eventDispatcher));
    }
}
