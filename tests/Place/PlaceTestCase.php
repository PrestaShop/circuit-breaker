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

use PHPUnit\Framework\TestCase;

/**
 * Helper to share fixtures accross Place tests.
 */
class PlaceTestCase extends TestCase
{
    public function getFixtures(): array
    {
        return [
            '0_0_0' => [0, 0.0, 0],
            '1_100_0' => [1, 100.0, 0],
            '3_0.6_3' => [3, 0.6, 3],
        ];
    }

    public function getArrayFixtures(): array
    {
        return [
            'assoc_array' => [[
                'timeout' => 3,
                'threshold' => 2,
                'failures' => 1,
            ]],
        ];
    }

    public function getInvalidFixtures(): array
    {
        return [
            'minus1_minus1.0_minus2' => [-1, -1.0, -2],
        ];
    }

    public function getInvalidArrayFixtures(): array
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
