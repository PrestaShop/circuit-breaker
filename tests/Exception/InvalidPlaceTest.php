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

namespace Tests\PrestaShop\CircuitBreaker\Exception;

use PHPUnit\Framework\TestCase;
use PrestaShop\CircuitBreaker\Exception\InvalidPlaceException;

class InvalidPlaceTest extends TestCase
{
    public function testCreation()
    {
        $invalidPlace = new InvalidPlaceException();

        $this->assertInstanceOf(InvalidPlaceException::class, $invalidPlace);
    }

    /**
     * @dataProvider getSettings
     *
     * @param array $settings
     * @param string $expectedExceptionMessage
     */
    public function testInvalidSettings($settings, $expectedExceptionMessage)
    {
        $invalidPlace = InvalidPlaceException::invalidSettings(
            $settings[0], // failures
            $settings[1], // timeout
            $settings[2]  // threshold
        );

        $this->assertSame($invalidPlace->getMessage(), $expectedExceptionMessage);
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return [
            'all_invalid_settings' => [
                ['0', '1', null],
                'Invalid settings for Place' . PHP_EOL .
                'Excepted failures to be a positive integer, got string (0)' . PHP_EOL .
                'Excepted timeout to be a float, got string (1)' . PHP_EOL .
                'Excepted threshold to be a positive integer, got NULL' . PHP_EOL,
            ],
            '2_invalid_settings' => [
                [0, '1', null],
                'Invalid settings for Place' . PHP_EOL .
                'Excepted timeout to be a float, got string (1)' . PHP_EOL .
                'Excepted threshold to be a positive integer, got NULL' . PHP_EOL,
            ],
            '1_invalid_settings' => [
                [0, '1', 2],
                'Invalid settings for Place' . PHP_EOL .
                'Excepted timeout to be a float, got string (1)' . PHP_EOL,
            ],
            'all_valid_settings' => [
                [0, 1.1, 2],
                'Invalid settings for Place' . PHP_EOL,
            ],
        ];
    }
}
