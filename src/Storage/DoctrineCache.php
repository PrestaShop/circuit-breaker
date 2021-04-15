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

declare(strict_types = 1);

namespace PrestaShop\CircuitBreaker\Storage;

use Doctrine\Common\Cache\CacheProvider;
use PrestaShop\CircuitBreaker\Contract\StorageInterface;
use PrestaShop\CircuitBreaker\Contract\TransactionInterface;
use PrestaShop\CircuitBreaker\Exception\TransactionNotFoundException;

/**
 * Implementation of Storage using the Doctrine Cache.
 */
class DoctrineCache implements StorageInterface
{
    /** @var CacheProvider */
    private $cacheProvider;

    public function __construct(CacheProvider $cacheProvider)
    {
        $this->cacheProvider = $cacheProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function saveTransaction(string $service, TransactionInterface $transaction): bool
    {
        $key = $this->getKey($service);

        return $this->cacheProvider->save($key, $transaction);
    }

    /**
     * {@inheritdoc}
     */
    public function getTransaction(string $service): TransactionInterface
    {
        $key = $this->getKey($service);

        if ($this->hasTransaction($service)) {
            return $this->cacheProvider->fetch($key);
        }

        throw new TransactionNotFoundException();
    }

    /**
     * {@inheritdoc}
     */
    public function hasTransaction(string $service): bool
    {
        $key = $this->getKey($service);

        return $this->cacheProvider->contains($key);
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): bool
    {
        return $this->cacheProvider->deleteAll();
    }

    /**
     * Helper method to properly store the transaction.
     *
     * @param string $service the service URI
     *
     * @return string the transaction unique identifier
     */
    private function getKey(string $service): string
    {
        return md5($service);
    }
}
