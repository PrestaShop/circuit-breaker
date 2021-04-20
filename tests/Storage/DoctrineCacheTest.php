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
    protected function setUp(): void
    {
        $this->doctrineCache = new DoctrineCache(
            new FilesystemCache(sys_get_temp_dir() . '/ps__circuit_breaker')
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $filesystemAdapter = new FilesystemCache(sys_get_temp_dir() . '/ps__circuit_breaker');
        $filesystemAdapter->deleteAll();
    }

    public function testCreation(): void
    {
        $doctrineCache = new DoctrineCache(
            new FilesystemCache(sys_get_temp_dir() . '/ps__circuit_breaker')
        );

        $this->assertInstanceOf(DoctrineCache::class, $doctrineCache);
    }

    /**
     * @depends testCreation
     */
    public function testSaveTransaction(): void
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
    public function testHasTransaction(): void
    {
        $this->doctrineCache->saveTransaction('http://test.com', $this->createMock(TransactionInterface::class));

        $this->assertTrue($this->doctrineCache->hasTransaction('http://test.com'));
    }

    /**
     * @depends testCreation
     * @depends testSaveTransaction
     * @depends testHasTransaction
     */
    public function testGetTransaction(): void
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
    public function testGetNotFoundTransactionThrowsAnException(): void
    {
        $this->expectException(TransactionNotFoundException::class);

        $this->doctrineCache->getTransaction('http://test.com');
    }

    /**
     * @depends testSaveTransaction
     * @depends testGetTransaction
     */
    public function testClear(): void
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
