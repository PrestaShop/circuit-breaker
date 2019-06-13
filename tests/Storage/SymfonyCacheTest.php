<?php

namespace Tests\PrestaShop\CircuitBreaker\Storage;

use PHPUnit\Framework\TestCase;
use PrestaShop\CircuitBreaker\Contract\TransactionInterface;
use PrestaShop\CircuitBreaker\Exception\TransactionNotFoundException;
use PrestaShop\CircuitBreaker\Storage\SymfonyCache;
use Symfony\Component\Cache\Simple\FilesystemCache;

class SymfonyCacheTest extends TestCase
{
    /**
     * @var SymfonyCache the Symfony Cache storage
     */
    private $symfonyCache;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->symfonyCache = new SymfonyCache(
            new FilesystemCache('ps__circuit_breaker', 20)
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        $filesystemAdapter = new FilesystemCache('ps__circuit_breaker', 20);
        $filesystemAdapter->clear();
    }

    public function testCreation()
    {
        $symfonyCache = new SymfonyCache(
            new FilesystemCache('ps__circuit_breaker')
        );

        $this->assertInstanceOf(SymfonyCache::class, $symfonyCache);
    }

    /**
     * @depends testCreation
     */
    public function testSaveTransaction()
    {
        $operation = $this->symfonyCache->saveTransaction(
            'http://test.com',
            $this->createMock(TransactionInterface::class)
        );

        $this->assertTrue($operation);
    }

    /**
     * @depends testCreation
     * @depends testSaveTransaction
     */
    public function testHasTransaction()
    {
        $this->symfonyCache->saveTransaction('http://test.com', $this->createMock(TransactionInterface::class));

        $this->assertTrue($this->symfonyCache->hasTransaction('http://test.com'));
    }

    /**
     * @depends testCreation
     * @depends testSaveTransaction
     * @depends testHasTransaction
     */
    public function testGetTransaction()
    {
        $translationStub = $this->createMock(TransactionInterface::class);
        $this->symfonyCache->saveTransaction('http://test.com', $translationStub);

        $transaction = $this->symfonyCache->getTransaction('http://test.com');

        $this->assertEquals($transaction, $translationStub);
    }

    /**
     * @depends testCreation
     * @depends testGetTransaction
     * @depends testHasTransaction
     */
    public function testGetNotFoundTransactionThrowsAnException()
    {
        $this->expectException(TransactionNotFoundException::class);

        $this->symfonyCache->getTransaction('http://test.com');
    }

    /**
     * @depends testSaveTransaction
     * @depends testGetTransaction
     */
    public function testClear()
    {
        $translationStub = $this->createMock(TransactionInterface::class);
        $this->symfonyCache->saveTransaction('http://a.com', $translationStub);
        $this->symfonyCache->saveTransaction('http://b.com', $translationStub);

        // We have stored 2 transactions
        $this->assertTrue($this->symfonyCache->clear());
        $this->expectException(TransactionNotFoundException::class);

        $this->symfonyCache->getTransaction('http://a.com');
    }
}
