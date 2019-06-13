<?php

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
    protected function setUp()
    {
        $simpleArray = new SimpleArray();
        $simpleArray::$transactions = [];
    }

    /**
     * @return void
     */
    public function testCreation()
    {
        $simpleArray = new SimpleArray();

        $this->assertCount(0, $simpleArray::$transactions);
        $this->assertInstanceOf(SimpleArray::class, $simpleArray);
    }

    /**
     * @depends testCreation
     *
     * @return void
     */
    public function testSaveTransaction()
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
     *
     * @return void
     */
    public function testHasTransaction()
    {
        $simpleArray = new SimpleArray();
        $simpleArray->saveTransaction('http://test.com', $this->createMock(TransactionInterface::class));

        $this->assertTrue($simpleArray->hasTransaction('http://test.com'));
    }

    /**
     * @depends testCreation
     * @depends testSaveTransaction
     * @depends testHasTransaction
     *
     * @return void
     */
    public function testGetTransaction()
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
     *
     * @return void
     */
    public function testGetNotFoundTransactionThrowsAnException()
    {
        $this->expectException(TransactionNotFoundException::class);

        $simpleArray = new SimpleArray();
        $simpleArray->getTransaction('http://test.com');
    }

    /**
     * @depends testSaveTransaction
     * @depends testGetTransaction
     *
     * @return void
     */
    public function testClear()
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
