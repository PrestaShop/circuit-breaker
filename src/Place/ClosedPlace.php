<?php

namespace PrestaShop\CircuitBreaker\Place;

use PrestaShop\CircuitBreaker\State;

final class ClosedPlace extends AbstractPlace
{
    /**
     * {@inheritdoc}
     */
    public function getState()
    {
        return State::CLOSED_STATE;
    }
}
