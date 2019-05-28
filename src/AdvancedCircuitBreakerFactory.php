<?php

namespace PrestaShop\CircuitBreaker;

use PrestaShop\CircuitBreaker\Contracts\Factory;
use PrestaShop\CircuitBreaker\Contracts\FactorySettingsInterface;
use PrestaShop\CircuitBreaker\Places\ClosedPlace;
use PrestaShop\CircuitBreaker\Places\HalfOpenPlace;
use PrestaShop\CircuitBreaker\Places\OpenPlace;
use PrestaShop\CircuitBreaker\Clients\GuzzleClient;
use PrestaShop\CircuitBreaker\Storages\SimpleArray;
use PrestaShop\CircuitBreaker\Systems\MainSystem;
use PrestaShop\CircuitBreaker\Transitions\NullDispatcher;

/**
 * Advanced implementation of Circuit Breaker Factory
 * Used to create an AdvancedCircuitBreaker instance.
 */
final class AdvancedCircuitBreakerFactory implements Factory
{
    /** @var FactorySettingsInterface */
    private $defaultSettings;

    /**
     * @param FactorySettingsInterface|null $defaultSettings
     */
    public function __construct(FactorySettingsInterface $defaultSettings = null)
    {
        $this->defaultSettings = $defaultSettings;
    }

    /**
     * {@inheritdoc}
     */
    public function create(FactorySettingsInterface $settings)
    {
        $settings = null !== $this->defaultSettings ? FactorySettings::merge($this->defaultSettings, $settings) : $settings;

        $closedPlace = new ClosedPlace($settings->getFailures(), $settings->getTimeout(), 0);
        $openPlace = new OpenPlace(0, 0, $settings->getThreshold());
        $halfOpenPlace = new HalfOpenPlace($settings->getFailures(), $settings->getStrippedTimeout(), 0);
        $system = new MainSystem($closedPlace, $halfOpenPlace, $openPlace);

        if (null !== $settings->getClient()) {
            $client = $settings->getClient();
        } else {
            $client = new GuzzleClient($settings->getClientSettings());
        }

        $storage = null !== $settings->getStorage() ? $settings->getStorage() : new SimpleArray();
        $dispatcher = null !== $settings->getDispatcher() ? $settings->getDispatcher() : new NullDispatcher();

        return new AdvancedCircuitBreaker(
            $system,
            $client,
            $storage,
            $dispatcher
        );
    }
}
