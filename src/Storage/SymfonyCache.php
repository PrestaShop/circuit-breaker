<?php

namespace PrestaShop\CircuitBreaker\Storage;

use PrestaShop\CircuitBreaker\Contract\StorageInterface;
use PrestaShop\CircuitBreaker\Contract\TransactionInterface;
use PrestaShop\CircuitBreaker\Exception\TransactionNotFoundException;
use Psr\SimpleCache\CacheInterface;

/**
 * Implementation of Storage using the Symfony Cache Component.
 */
final class SymfonyCache implements StorageInterface
{
    /**
     * @var CacheInterface the Symfony Cache
     */
    private $symfonyCache;

    public function __construct(CacheInterface $symfonyCache)
    {
        $this->symfonyCache = $symfonyCache;
    }

    /**
     * {@inheritdoc}
     */
    public function saveTransaction($service, TransactionInterface $transaction)
    {
        $key = $this->getKey($service);

        return $this->symfonyCache->set($key, $transaction);
    }

    /**
     * {@inheritdoc}
     */
    public function getTransaction($service)
    {
        $key = $this->getKey($service);

        if ($this->hasTransaction($service)) {
            return $this->symfonyCache->get($key);
        }

        throw new TransactionNotFoundException();
    }

    /**
     * {@inheritdoc}
     */
    public function hasTransaction($service)
    {
        $key = $this->getKey($service);

        return $this->symfonyCache->has($key);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        return $this->symfonyCache->clear();
    }

    /**
     * Helper method to properly store the transaction.
     *
     * @param string $service the service URI
     *
     * @return string the transaction unique identifier
     */
    private function getKey($service)
    {
        return md5($service);
    }
}
