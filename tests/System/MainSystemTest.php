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

namespace Tests\PrestaShop\CircuitBreaker\System;

use PHPUnit\Framework\TestCase;
use PrestaShop\CircuitBreaker\Contract\PlaceInterface;
use PrestaShop\CircuitBreaker\Place\ClosedPlace;
use PrestaShop\CircuitBreaker\Place\HalfOpenPlace;
use PrestaShop\CircuitBreaker\Place\OpenPlace;
use PrestaShop\CircuitBreaker\State;
use PrestaShop\CircuitBreaker\System\MainSystem;

class MainSystemTest extends TestCase
{
    public function testCreation()
    {
        $openPlace = new OpenPlace(1, 1, 1);
        $halfOpenPlace = new HalfOpenPlace(1, 1, 1);
        $closedPlace = new ClosedPlace(1, 1, 1);

        $mainSystem = new MainSystem(
            $openPlace,
            $halfOpenPlace,
            $closedPlace
        );

        $this->assertInstanceOf(MainSystem::class, $mainSystem);
    }

    /**
     * @depends testCreation
     */
    public function testGetInitialPlace()
    {
        $mainSystem = $this->createMainSystem();
        $initialPlace = $mainSystem->getInitialPlace();

        $this->assertInstanceOf(PlaceInterface::class, $initialPlace);
        $this->assertSame(State::CLOSED_STATE, $initialPlace->getState());
    }

    /**
     * @depends testCreation
     */
    public function testGetPlaces()
    {
        $mainSystem = $this->createMainSystem();
        $places = $mainSystem->getPlaces();

        $this->assertInternalType('array', $places);
        $this->assertCount(3, $places);

        foreach ($places as $place) {
            $this->assertInstanceOf(PlaceInterface::class, $place);
        }
    }

    /**
     * Returns an instance of MainSystem for tests.
     *
     * @return MainSystem
     */
    private function createMainSystem()
    {
        $openPlace = new OpenPlace(1, 1, 1);
        $halfOpenPlace = new HalfOpenPlace(1, 1, 1);
        $closedPlace = new ClosedPlace(1, 1, 1);

        return new MainSystem(
            $openPlace,
            $halfOpenPlace,
            $closedPlace
        );
    }
}
