<?php

namespace PrestaShop\CircuitBreaker;

use PrestaShop\CircuitBreaker\Contracts\Client;
use PrestaShop\CircuitBreaker\Contracts\Factory;
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
    /** @var array */
    private $defaultSettings;

    /**
     * @param array $defaultSettings
     */
    public function __construct(array $defaultSettings = [])
    {
        $this->defaultSettings = $defaultSettings;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $settings)
    {
        $settings = array_merge($this->defaultSettings, $settings);
        $openPlace = OpenPlace::fromArray($settings['open']);
        $halfOpenPlace = HalfOpenPlace::fromArray($settings['half_open']);
        $closedPlace = ClosedPlace::fromArray($settings['closed']);
        $system = new MainSystem($closedPlace, $halfOpenPlace, $openPlace);

        if (array_key_exists('client', $settings)) {
            if ($settings['client'] instanceof Client) {
                $client = $settings['client'];
            } else {
                $client = new GuzzleClient($settings['client']);
            }
        } else {
            $client = new GuzzleClient();
        }

        $storage = array_key_exists('storage', $settings) ? $settings['storage'] : new SimpleArray();
        $dispatcher = array_key_exists('dispatcher', $settings) ? $settings['dispatcher'] : new NullDispatcher();

        return new AdvancedCircuitBreaker(
            $system,
            $client,
            $storage,
            $dispatcher
        );
    }
}
