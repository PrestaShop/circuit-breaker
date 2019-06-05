<?php

namespace PrestaShop\CircuitBreaker\Contracts;

/**
 * Ease the creation of the Circuit Breaker.
 */
interface FactoryInterface
{
    /**
     * @param FactorySettingsInterface $settings the settings for the Places
     *
     * @return CircuitBreakerInterface
     */
    public function create(FactorySettingsInterface $settings);
}
