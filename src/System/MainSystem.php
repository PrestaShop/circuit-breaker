<?php

namespace PrestaShop\CircuitBreaker\System;

use PrestaShop\CircuitBreaker\Contract\PlaceInterface;
use PrestaShop\CircuitBreaker\Contract\SystemInterface;
use PrestaShop\CircuitBreaker\State;

/**
 * Implement the system described by the documentation.
 * The main system is built with 3 places:
 * - A Closed place
 * - A Half Open Place
 * - An Open Place
 */
final class MainSystem implements SystemInterface
{
    /**
     * @var PlaceInterface[]
     */
    private $places;

    public function __construct(
        PlaceInterface $closedPlace,
        PlaceInterface $halfOpenPlace,
        PlaceInterface $openPlace
    ) {
        $this->places = [
            $closedPlace->getState() => $closedPlace,
            $halfOpenPlace->getState() => $halfOpenPlace,
            $openPlace->getState() => $openPlace,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getInitialPlace()
    {
        return $this->places[State::CLOSED_STATE];
    }

    /**
     * {@inheritdoc}
     */
    public function getPlaces()
    {
        return $this->places;
    }
}
