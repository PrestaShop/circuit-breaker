<?php

namespace PrestaShop\CircuitBreaker;

use PrestaShop\CircuitBreaker\Contracts\Place;
use PrestaShop\CircuitBreaker\Contracts\Client;
use PrestaShop\CircuitBreaker\Systems\MainSystem;
use PrestaShop\CircuitBreaker\Storages\SimpleArray;
use PrestaShop\CircuitBreaker\Exceptions\UnavailableServiceException;

/**
 * Main implementation of Circuit Breaker.
 */
final class SimpleCircuitBreaker extends PartialCircuitBreaker
{
    public function __construct(
        Place $openPlace,
        Place $halfOpenPlace,
        Place $closedPlace,
        Client $client
    ) {
        $system = new MainSystem($closedPlace, $halfOpenPlace, $openPlace);

        parent::__construct($system, $client, new SimpleArray());
    }

    /**
     * {@inheritdoc}
     */
    public function call($service, callable $fallback)
    {
        $transaction = $this->initTransaction($service);

        if ($this->isOpened()) {
            if ($this->canAccessService($transaction)) {
                $this->moveStateTo(States::HALF_OPEN_STATE, $service);
            }

            return \call_user_func($fallback);
        }

        // Use do..while loop to allow at least one request (even when 0 retries have set
        // in an HalfOpen state for example, or it will stay stuck in this state)
        do {
            try {
                $response = $this->request($service);
                $this->moveStateTo(States::CLOSED_STATE, $service);

                return $response;
            } catch (UnavailableServiceException $exception) {
                $transaction->incrementFailures();
                $this->storage->saveTransaction($service, $transaction);
            }
        } while ($this->isAllowedToRetry($transaction));

        $this->moveStateTo(States::OPEN_STATE, $service);

        return \call_user_func($fallback);
    }
}
