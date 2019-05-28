<?php

namespace PrestaShop\CircuitBreaker\Contracts;

/**
 * Ease the creation of the Circuit Breaker.
 */
interface Factory
{
    /**
     * @param FactorySettingsInterface $settings the settings for the Places
     *
     * @return CircuitBreaker
     */
    public function create(FactorySettingsInterface $settings);
}
