<?php

namespace Tests\PrestaShop\CircuitBreaker\Place;

use PrestaShop\CircuitBreaker\Exception\InvalidPlaceException;
use PrestaShop\CircuitBreaker\Place\OpenPlace;
use PrestaShop\CircuitBreaker\State;

class OpenPlaceTest extends PlaceTestCase
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
        $openPlace = new OpenPlace($failures, $timeout, $threshold);

        $this->assertSame($failures, $openPlace->getFailures());
        $this->assertSame($timeout, $openPlace->getTimeout());
        $this->assertSame($threshold, $openPlace->getThreshold());
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

        new OpenPlace($failures, $timeout, $threshold);
    }

    /**
     * @dataProvider getArrayFixtures
     *
     * @param array $settings
     */
    public function testFromArrayWith(array $settings)
    {
        $openPlace = OpenPlace::fromArray($settings);

        $this->assertNotNull($openPlace);
    }

    /**
     * @dataProvider getInvalidArrayFixtures
     *
     * @param array $settings
     */
    public function testFromArrayWithInvalidValues(array $settings)
    {
        $this->expectException(InvalidPlaceException::class);

        OpenPlace::fromArray($settings);
    }

    public function testGetExpectedState()
    {
        $openPlace = new OpenPlace(1, 1, 1);

        $this->assertSame(State::OPEN_STATE, $openPlace->getState());
    }
}
