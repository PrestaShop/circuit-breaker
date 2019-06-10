<?php

namespace Tests\PrestaShop\CircuitBreaker;

use PHPUnit\Framework\TestCase;
use PrestaShop\CircuitBreaker\Contract\FactorySettingsInterface;
use PrestaShop\CircuitBreaker\FactorySettings;
use PrestaShop\CircuitBreaker\SimpleCircuitBreaker;
use PrestaShop\CircuitBreaker\SimpleCircuitBreakerFactory;

class SimpleCircuitBreakerFactoryTest extends TestCase
{
    /**
     * @return void
     */
    public function testCreation()
    {
        $factory = new SimpleCircuitBreakerFactory();

        $this->assertInstanceOf(SimpleCircuitBreakerFactory::class, $factory);
    }

    /**
     * @depends testCreation
     * @dataProvider getSettings
     *
     * @param FactorySettingsInterface $settings the Circuit Breaker settings
     *
     * @return void
     */
    public function testCircuitBreakerCreation(FactorySettingsInterface $settings)
    {
        $factory = new SimpleCircuitBreakerFactory();
        $circuitBreaker = $factory->create($settings);

        $this->assertInstanceOf(SimpleCircuitBreaker::class, $circuitBreaker);
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return [
            [
                (new FactorySettings(2, 0.1, 10))
                    ->setStrippedTimeout(0.2)
                    ->setStrippedFailures(1),
            ],
            [
                (new FactorySettings(2, 0.1, 10))
                    ->setStrippedTimeout(0.2)
                    ->setStrippedFailures(1)
                    ->setClientOptions(['proxy' => '192.168.16.1:10']),
            ],
        ];
    }
}
