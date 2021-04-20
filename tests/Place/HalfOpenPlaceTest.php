<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

declare(strict_types=1);

namespace Tests\PrestaShop\CircuitBreaker\Place;

use PrestaShop\CircuitBreaker\Exception\InvalidPlaceException;
use PrestaShop\CircuitBreaker\Place\HalfOpenPlace;
use PrestaShop\CircuitBreaker\State;

class HalfOpenPlaceTest extends PlaceTestCase
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
        $halfOpenPlace = new HalfOpenPlace($failures, $timeout, $threshold);

        $this->assertSame($failures, $halfOpenPlace->getFailures());
        $this->assertSame($timeout, $halfOpenPlace->getTimeout());
        $this->assertSame($threshold, $halfOpenPlace->getThreshold());
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

        new HalfOpenPlace($failures, $timeout, $threshold);
    }

    public function testGetExpectedState()
    {
        $halfOpenPlace = new HalfOpenPlace(1, 1, 1);

        $this->assertSame(State::HALF_OPEN_STATE, $halfOpenPlace->getState());
    }
}
