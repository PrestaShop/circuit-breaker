<?php

namespace PrestaShop\CircuitBreaker\Contract;

/**
 * The System define the places available
 * for the Circuit Breaker and the initial Place.
 */
interface SystemInterface
{
    /**
     * @return PlaceInterface[] the list of places of the system
     */
    public function getPlaces();

    /**
     * @return PlaceInterface the initial place of the system
     */
    public function getInitialPlace();
}
