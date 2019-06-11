<?php

namespace Tests\PrestaShop\CircuitBreaker\Storage;

use Doctrine\Common\Cache\FilesystemCache;
use PHPUnit\Framework\TestCase;
use PrestaShop\CircuitBreaker\Contract\StorageInterface;
use PrestaShop\CircuitBreaker\Contract\TransactionInterface;
use PrestaShop\CircuitBreaker\Exception\TransactionNotFoundException;
use PrestaShop\CircuitBreaker\Storage\DoctrineCache;

class DoctrineCacheTest extends TestCase
{
    /**
     * @var StorageInterface the Doctrine Cache storage
     */
    private $doctrineCache;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->doctrineCache = new DoctrineCache(
            new FilesystemCache(sys_get_temp_dir() . '/ps__circuit_breaker')
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        $filesystemAdapter = new FilesystemCache(sys_get_temp_dir() . '/ps__circuit_breaker');
        $filesystemAdapter->deleteAll();
    }

    public function testCreation()
    {
        $doctrineCache = new DoctrineCache(
            new FilesystemCache(sys_get_temp_dir() . '/ps__circuit_breaker')
        );

        $this->assertInstanceOf(DoctrineCache::class, $doctrineCache);
    }

    /**
     * @depends testCreation
     */
    public function testSaveTransaction()
    {
        $operation = $this->doctrineCache->saveTransaction(
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
        $this->doctrineCache->saveTransaction('http://test.com', $this->createMock(TransactionInterface::class));

        $this->assertTrue($this->doctrineCache->hasTransaction('http://test.com'));
    }

    /**
     * @depends testCreation
     * @depends testSaveTransaction
     * @depends testHasTransaction
     */
    public function testGetTransaction()
    {
        $translationStub = $this->createMock(TransactionInterface::class);
        $this->doctrineCache->saveTransaction('http://test.com', $translationStub);

        $transaction = $this->doctrineCache->getTransaction('http://test.com');

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

        $this->doctrineCache->getTransaction('http://test.com');
    }

    /**
     * @depends testSaveTransaction
     * @depends testGetTransaction
     */
    public function testClear()
    {
        $translationStub = $this->createMock(TransactionInterface::class);
        $this->doctrineCache->saveTransaction('http://a.com', $translationStub);
        $this->doctrineCache->saveTransaction('http://b.com', $translationStub);

        // We have stored 2 transactions
        $this->assertTrue($this->doctrineCache->clear());
        $this->expectException(TransactionNotFoundException::class);

        $this->doctrineCache->getTransaction('http://a.com');
    }
}
