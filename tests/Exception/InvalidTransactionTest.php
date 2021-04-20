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
use PrestaShop\CircuitBreaker\Exception\InvalidTransactionException;

class InvalidTransactionTest extends TestCase
{
    public function testCreation(): void
    {
        $invalidPlace = new InvalidTransactionException();

        $this->assertInstanceOf(InvalidTransactionException::class, $invalidPlace);
    }

    /**
     * @dataProvider getParameters
     *
     * @param array $parameters
     * @param string $expectedExceptionMessage
     */
    public function testInvalidParameters($parameters, $expectedExceptionMessage): void
    {
        $invalidPlace = InvalidTransactionException::invalidParameters(
            $parameters[0], // service
            $parameters[1], // failures
            $parameters[2], // state
            $parameters[3]  // threshold
        );

        $this->assertSame($invalidPlace->getMessage(), $expectedExceptionMessage);
    }

    public function getParameters(): array
    {
        return [
            'all_invalid_parameters' => [
                [100, '0', null, 'toto'],
                'Invalid parameters for Transaction' . PHP_EOL .
                'Excepted service to be an URI, got integer (100)' . PHP_EOL .
                'Excepted failures to be a positive integer, got string (0)' . PHP_EOL .
                'Excepted state to be a string, got NULL' . PHP_EOL .
                'Excepted threshold to be a positive integer, got string (toto)' . PHP_EOL,
            ],
            '3_invalid_parameters' => [
                ['http://www.prestashop.com', '1', null, 'toto'],
                'Invalid parameters for Transaction' . PHP_EOL .
                'Excepted failures to be a positive integer, got string (1)' . PHP_EOL .
                'Excepted state to be a string, got NULL' . PHP_EOL .
                'Excepted threshold to be a positive integer, got string (toto)' . PHP_EOL,
            ],
            '2_invalid_parameters' => [
                ['http://www.prestashop.com', 10, null, null],
                'Invalid parameters for Transaction' . PHP_EOL .
                'Excepted state to be a string, got NULL' . PHP_EOL .
                'Excepted threshold to be a positive integer, got NULL' . PHP_EOL,
            ],
            'none_invalid' => [
                ['http://www.prestashop.com', 10, 'CLOSED_STATE', 1],
                'Invalid parameters for Transaction' . PHP_EOL,
            ],
        ];
    }
}
