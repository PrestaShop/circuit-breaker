<?php

namespace PrestaShop\CircuitBreaker;

use PrestaShop\CircuitBreaker\Contract\ClientInterface;
use PrestaShop\CircuitBreaker\Contract\FactoryInterface;
use PrestaShop\CircuitBreaker\Contract\FactorySettingsInterface;
use PrestaShop\CircuitBreaker\Contract\StorageInterface;
use PrestaShop\CircuitBreaker\Contract\TransitionDispatcherInterface;
use PrestaShop\CircuitBreaker\Place\ClosedPlace;
use PrestaShop\CircuitBreaker\Place\HalfOpenPlace;
use PrestaShop\CircuitBreaker\Place\OpenPlace;
use PrestaShop\CircuitBreaker\Client\GuzzleClient;
use PrestaShop\CircuitBreaker\Storage\SimpleArray;
use PrestaShop\CircuitBreaker\System\MainSystem;
use PrestaShop\CircuitBreaker\Transition\NullDispatcher;

/**
 * Advanced implementation of Circuit Breaker Factory
 * Used to create an AdvancedCircuitBreaker instance.
 */
final class AdvancedCircuitBreakerFactory implements FactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(FactorySettingsInterface $settings)
    {
        $closedPlace = new ClosedPlace($settings->getFailures(), $settings->getTimeout(), 0);
        $openPlace = new OpenPlace(0, 0, $settings->getThreshold());
        $halfOpenPlace = new HalfOpenPlace($settings->getFailures(), $settings->getStrippedTimeout(), 0);
        $system = new MainSystem($closedPlace, $halfOpenPlace, $openPlace);

        /** @var ClientInterface $client */
        $client = $settings->getClient() ?: new GuzzleClient($settings->getClientOptions());
        /** @var StorageInterface $storage */
        $storage = $settings->getStorage() ?: new SimpleArray();
        /** @var TransitionDispatcherInterface $dispatcher */
        $dispatcher = $settings->getDispatcher() ?: new NullDispatcher();

        $circuitBreaker = new AdvancedCircuitBreaker(
            $system,
            $client,
            $storage,
            $dispatcher
        );
        if (null !== $settings->getDefaultFallback()) {
            $circuitBreaker->setDefaultFallback($settings->getDefaultFallback());
        }

        return $circuitBreaker;
    }
}
