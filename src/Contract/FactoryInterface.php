<?php

namespace PrestaShop\CircuitBreaker\Contract;

/**
 * Ease the creation of the Circuit Breaker.
 */
interface FactoryInterface
{
    /**
     * @param FactorySettingsInterface $settings the settings for the Place
     *
     * @return CircuitBreakerInterface
     */
    public function create(FactorySettingsInterface $settings);
}
