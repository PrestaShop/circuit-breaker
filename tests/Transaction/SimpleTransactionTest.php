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

namespace Tests\PrestaShop\CircuitBreaker\Transaction;

use DateTime;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PrestaShop\CircuitBreaker\Contract\PlaceInterface;
use PrestaShop\CircuitBreaker\Transaction\SimpleTransaction;

class SimpleTransactionTest extends TestCase
{
    public function testCreation(): void
    {
        $placeStub = $this->createPlaceStub();

        $simpleTransaction = new SimpleTransaction(
            'http://some-uri.domain',
            0,
            $placeStub->getState(),
            2
        );

        $this->assertInstanceOf(SimpleTransaction::class, $simpleTransaction);
    }

    /**
     * @depends testCreation
     */
    public function testGetService(): void
    {
        $simpleTransaction = $this->createSimpleTransaction();

        $this->assertSame('http://some-uri.domain', $simpleTransaction->getService());
    }

    /**
     * @depends testCreation
     */
    public function testGetFailures(): void
    {
        $simpleTransaction = $this->createSimpleTransaction();

        $this->assertSame(0, $simpleTransaction->getFailures());
    }

    /**
     * @depends testCreation
     */
    public function testGetState(): void
    {
        $simpleTransaction = $this->createSimpleTransaction();

        $this->assertSame('FAKE_STATE', $simpleTransaction->getState());
    }

    /**
     * @depends testCreation
     */
    public function testGetThresholdDateTime(): void
    {
        $simpleTransaction = $this->createSimpleTransaction();
        $expectedDateTime = (new DateTime('+2 second'))->format('d/m/Y H:i:s');
        $simpleTransactionDateTime = $simpleTransaction->getThresholdDateTime()->format('d/m/Y H:i:s');

        $this->assertSame($expectedDateTime, $simpleTransactionDateTime);
    }

    /**
     * @depends testCreation
     * @depends testGetFailures
     */
    public function testIncrementFailures(): void
    {
        $simpleTransaction = $this->createSimpleTransaction();
        $simpleTransaction->incrementFailures();

        $this->assertSame(1, $simpleTransaction->getFailures());
    }

    /**
     * @depends testCreation
     */
    public function testCreationFromPlaceHelper(): void
    {
        $simpleTransactionFromHelper = SimpleTransaction::createFromPlace(
            $this->createPlaceStub(),
            'http://some-uri.domain'
        );

        $simpleTransaction = $this->createSimpleTransaction();

        $this->assertSame($simpleTransactionFromHelper->getState(), $simpleTransaction->getState());
        $this->assertSame($simpleTransactionFromHelper->getFailures(), $simpleTransaction->getFailures());
        $fromPlaceDate = $simpleTransactionFromHelper->getThresholdDateTime()->format('d/m/Y H:i:s');
        $expectedDate = $simpleTransaction->getThresholdDateTime()->format('d/m/Y H:i:s');

        $this->assertSame($fromPlaceDate, $expectedDate);
    }

    /**
     * Returns an instance of SimpleTransaction for tests.
     */
    private function createSimpleTransaction(): SimpleTransaction
    {
        $placeStub = $this->createPlaceStub();

        return new SimpleTransaction(
            'http://some-uri.domain',
            0,
            $placeStub->getState(),
            2
        );
    }

    /**
     * Returns an instance of Place with State equals to "FAKE_STATE"
     * and threshold equals to 2.
     */
    private function createPlaceStub(): MockObject
    {
        $placeStub = $this->createMock(PlaceInterface::class);

        $placeStub->expects($this->any())
            ->method('getState')
            ->willReturn('FAKE_STATE')
        ;

        $placeStub->expects($this->any())
            ->method('getThreshold')
            ->willReturn(2)
        ;

        return $placeStub;
    }
}
