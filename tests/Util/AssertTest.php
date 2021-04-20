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

namespace Tests\PrestaShop\CircuitBreaker\Util;

use PHPUnit\Framework\TestCase;
use PrestaShop\CircuitBreaker\Util\Assert;
use stdClass;

class AssertTest extends TestCase
{
    /**
     * @dataProvider getValues
     *
     * @param mixed $value
     */
    public function testIsPositiveValue($value, bool $expected): void
    {
        $this->assertSame($expected, Assert::isPositiveValue($value));
    }

    /**
     * @dataProvider getURIs
     *
     * @param mixed $value
     */
    public function testIsURI($value, bool $expected): void
    {
        $this->assertSame($expected, Assert::isURI($value));
    }

    /**
     * @dataProvider getStrings
     *
     * @param mixed $value
     */
    public function testIsString($value, bool $expected): void
    {
        $this->assertSame($expected, Assert::isString($value));
    }

    public function getValues(): array
    {
        return [
            '0' => [0, true],
            'str_0' => ['0', false],
            'float' => [0.1, true],
            'stdclass' => [new stdClass(), false],
            'callable' => [
                function () {
                    return 0;
                },
                false,
            ],
            'negative' => [-1, false],
            'bool' => [false, false],
        ];
    }

    public function getURIs(): array
    {
        return [
            'valid' => ['http://www.prestashop.com', true],
            'int' => [0, false],
            'null' => [null, false],
            'bool' => [false, false],
            'local' => ['http://localhost', true],
            'ssh' => ['ssh://git@git.example.com/FOO/my_project.git', true],
        ];
    }

    public function getStrings(): array
    {
        return [
            'valid' => ['foo', true],
            'empty' => ['', false],
            'null' => [null, false],
            'bool' => [false, false],
            'stdclass' => [new stdClass(), false],
            'valid2' => ['INVALID_STATE', true],
        ];
    }
}
