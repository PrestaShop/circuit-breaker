<?php

namespace PrestaShop\CircuitBreaker\Place;

use PrestaShop\CircuitBreaker\State;

final class HalfOpenPlace extends AbstractPlace
{
    /**
     * {@inheritdoc}
     */
    public function getState()
    {
        return State::HALF_OPEN_STATE;
    }
}
