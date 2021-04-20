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

namespace Tests\PrestaShop\CircuitBreaker\Storage;

use PHPUnit\Framework\TestCase;
use PrestaShop\CircuitBreaker\Contract\TransactionInterface;
use PrestaShop\CircuitBreaker\Exception\TransactionNotFoundException;
use PrestaShop\CircuitBreaker\Storage\SimpleArray;

class SimpleArrayTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $simpleArray = new SimpleArray();
        $simpleArray::$transactions = [];
    }

    public function testCreation(): void
    {
        $simpleArray = new SimpleArray();

        $this->assertCount(0, $simpleArray::$transactions);
        $this->assertInstanceOf(SimpleArray::class, $simpleArray);
    }

    /**
     * @depends testCreation
     */
    public function testSaveTransaction(): void
    {
        $simpleArray = new SimpleArray();
        $operation = $simpleArray->saveTransaction(
            'http://test.com',
            $this->createMock(TransactionInterface::class)
        );
        $this->assertTrue($operation);
        $this->assertCount(1, $simpleArray::$transactions);
    }

    /**
     * @depends testCreation
     * @depends testSaveTransaction
     */
    public function testHasTransaction(): void
    {
        $simpleArray = new SimpleArray();
        $simpleArray->saveTransaction('http://test.com', $this->createMock(TransactionInterface::class));

        $this->assertTrue($simpleArray->hasTransaction('http://test.com'));
    }

    /**
     * @depends testCreation
     * @depends testSaveTransaction
     * @depends testHasTransaction
     */
    public function testGetTransaction(): void
    {
        $simpleArray = new SimpleArray();
        $translationStub = $this->createMock(TransactionInterface::class);
        $simpleArray->saveTransaction('http://test.com', $translationStub);

        $transaction = $simpleArray->getTransaction('http://test.com');

        $this->assertSame($transaction, $translationStub);
    }

    /**
     * @depends testCreation
     * @depends testGetTransaction
     * @depends testHasTransaction
     */
    public function testGetNotFoundTransactionThrowsAnException(): void
    {
        $this->expectException(TransactionNotFoundException::class);

        $simpleArray = new SimpleArray();
        $simpleArray->getTransaction('http://test.com');
    }

    /**
     * @depends testSaveTransaction
     * @depends testGetTransaction
     */
    public function testClear(): void
    {
        $simpleArray = new SimpleArray();
        $translationStub = $this->createMock(TransactionInterface::class);
        $simpleArray->saveTransaction('http://a.com', $translationStub);
        $simpleArray->saveTransaction('http://b.com', $translationStub);

        // We have stored 2 transactions
        $simpleArray->clear();
        $transactions = $simpleArray::$transactions;
        $this->assertEmpty($transactions);
    }
}
