<?php

namespace PrestaShop\CircuitBreaker;

use PrestaShop\CircuitBreaker\Contracts\FactoryInterface;
use PrestaShop\CircuitBreaker\Contracts\FactorySettingsInterface;
use PrestaShop\CircuitBreaker\Places\ClosedPlace;
use PrestaShop\CircuitBreaker\Places\HalfOpenPlace;
use PrestaShop\CircuitBreaker\Places\OpenPlace;
use PrestaShop\CircuitBreaker\Clients\GuzzleClient;

/**
 * Main implementation of Circuit Breaker Factory
 * Used to create a SimpleCircuitBreaker instance.
 */
final class SimpleCircuitBreakerFactory implements FactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(FactorySettingsInterface $settings)
    {
        $closedPlace = new ClosedPlace($settings->getFailures(), $settings->getTimeout(), 0);
        $openPlace = new OpenPlace(0, 0, $settings->getThreshold());
        $halfOpenPlace = new HalfOpenPlace($settings->getFailures(), $settings->getStrippedTimeout(), 0);

        if (null !== $settings->getClient()) {
            $client = $settings->getClient();
        } else {
            $client = new GuzzleClient($settings->getClientOptions());
        }

        return new SimpleCircuitBreaker(
            $openPlace,
            $halfOpenPlace,
            $closedPlace,
            $client
        );
    }
}
