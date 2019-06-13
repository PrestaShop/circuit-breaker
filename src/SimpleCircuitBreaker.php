<?php

namespace PrestaShop\CircuitBreaker;

use PrestaShop\CircuitBreaker\Contract\PlaceInterface;
use PrestaShop\CircuitBreaker\Contract\ClientInterface;
use PrestaShop\CircuitBreaker\System\MainSystem;
use PrestaShop\CircuitBreaker\Storage\SimpleArray;
use PrestaShop\CircuitBreaker\Exception\UnavailableServiceException;

/**
 * Main implementation of Circuit Breaker.
 */
final class SimpleCircuitBreaker extends PartialCircuitBreaker
{
    public function __construct(
        PlaceInterface $openPlace,
        PlaceInterface $halfOpenPlace,
        PlaceInterface $closedPlace,
        ClientInterface $client
    ) {
        $system = new MainSystem($closedPlace, $halfOpenPlace, $openPlace);

        parent::__construct($system, $client, new SimpleArray());
    }

    /**
     * {@inheritdoc}
     */
    public function call(
        $service,
        array $serviceParameters = [],
        callable $fallback = null
    ) {
        $transaction = $this->initTransaction($service);
        try {
            if ($this->isOpened()) {
                if (!$this->canAccessService($transaction)) {
                    return $this->callFallback($fallback);
                }

                $this->moveStateTo(State::HALF_OPEN_STATE, $service);
            }
            $response = $this->request($service, $serviceParameters);
            $this->moveStateTo(State::CLOSED_STATE, $service);

            return $response;
        } catch (UnavailableServiceException $exception) {
            $transaction->incrementFailures();
            $this->storage->saveTransaction($service, $transaction);
            if (!$this->isAllowedToRetry($transaction)) {
                $this->moveStateTo(State::OPEN_STATE, $service);

                return $this->callFallback($fallback);
            }

            return $this->call($service, $serviceParameters, $fallback);
        }
    }
}
