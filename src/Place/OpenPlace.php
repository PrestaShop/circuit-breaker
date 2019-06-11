<?php

namespace PrestaShop\CircuitBreaker\Place;

use PrestaShop\CircuitBreaker\State;

final class OpenPlace extends AbstractPlace
{
    /**
     * {@inheritdoc}
     */
    public function getState()
    {
        return State::OPEN_STATE;
    }
}
