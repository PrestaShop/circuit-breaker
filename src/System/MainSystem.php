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
    public function getInitialPlace(): PlaceInterface
    {
        return $this->places[State::CLOSED_STATE];
    }

    /**
     * {@inheritdoc}
     */
    public function getPlaces(): array
    {
        return $this->places;
    }
}
