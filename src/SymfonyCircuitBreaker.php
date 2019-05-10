<?php

namespace PrestaShop\CircuitBreaker;

use PrestaShop\CircuitBreaker\Contracts\Client;
use PrestaShop\CircuitBreaker\Contracts\System;
use PrestaShop\CircuitBreaker\Contracts\Storage;
use PrestaShop\CircuitBreaker\Transitions\EventDispatcher;
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

    public function __construct(
        System $system,
        Client $client,
        Storage $storage,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->eventDispatcher = $eventDispatcher;

        parent::__construct($system, $client, $storage, new EventDispatcher($eventDispatcher));
    }
}
