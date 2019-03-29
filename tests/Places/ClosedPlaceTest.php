<?php

namespace Tests\PrestaShop\CircuitBreaker\Places;

use PrestaShop\CircuitBreaker\Exceptions\InvalidPlaceException;
use PrestaShop\CircuitBreaker\Places\ClosedPlace;
use PrestaShop\CircuitBreaker\States;

class ClosedPlaceTest extends PlaceTestCase
{
    /**
     * @dataProvider getFixtures
     *
     * @param mixed $failures
     * @param mixed $timeout
     * @param mixed $threshold
     */
    public function testCreationWith($failures, $timeout, $threshold)
    {
        $closedPlace = new ClosedPlace($failures, $timeout, $threshold);

        $this->assertSame($failures, $closedPlace->getFailures());
        $this->assertSame($timeout, $closedPlace->getTimeout());
        $this->assertSame($threshold, $closedPlace->getThreshold());
    }

    /**
     * @dataProvider getInvalidFixtures
     *
     * @param mixed $failures
     * @param mixed $timeout
     * @param mixed $threshold
     */
    public function testCreationWithInvalidValues($failures, $timeout, $threshold)
    {
        $this->expectException(InvalidPlaceException::class);

        new ClosedPlace($failures, $timeout, $threshold);
    }

    public function testGetExpectedState()
    {
        $closedPlace = new ClosedPlace(1, 1, 1);

        $this->assertSame(States::CLOSED_STATE, $closedPlace->getState());
    }
}
