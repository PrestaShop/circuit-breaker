<?php

namespace Tests\PrestaShop\CircuitBreaker\Place;

use PHPUnit\Framework\TestCase;

/**
 * Helper to share fixtures accross Place tests.
 */
class PlaceTestCase extends TestCase
{
    /**
     * @return array
     */
    public function getFixtures()
    {
        return [
            '0_0_0' => [0, 0, 0],
            '1_100_0' => [1, 100, 0],
            '3_0.6_3' => [3, 0.6, 3],
        ];
    }

    /**
     * @return array
     */
    public function getArrayFixtures()
    {
        return [
            'assoc_array' => [[
                'timeout' => 3,
                'threshold' => 2,
                'failures' => 1,
            ]],
        ];
    }

    /**
     * @return array
     */
    public function getInvalidFixtures()
    {
        return [
            'minus1_null_false' => [-1, null, false],
            '3_0.6_3.14' => [3, 0.6, 3.14],
        ];
    }

    /**
     * @return array
     */
    public function getInvalidArrayFixtures()
    {
        return [
            'invalid_indexes' => [[
                0 => 3,
                1 => 2,
                4 => 1,
            ]],
            'invalid_keys' => [[
                'timeout' => 3,
                'max_wait' => 2,
                'failures' => 1,
            ]],
        ];
    }
}
