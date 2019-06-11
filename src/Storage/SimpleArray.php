<?php

namespace PrestaShop\CircuitBreaker\Storage;

use PrestaShop\CircuitBreaker\Contract\StorageInterface;
use PrestaShop\CircuitBreaker\Contract\TransactionInterface;
use PrestaShop\CircuitBreaker\Exception\TransactionNotFoundException;

/**
 * Very simple implementation of Storage using a simple PHP array.
 */
final class SimpleArray implements StorageInterface
{
    /**
     * @var array the circuit breaker transactions
     */
    public static $transactions = [];

    /**
     * {@inheritdoc}
     */
    public function saveTransaction($service, TransactionInterface $transaction)
    {
        $key = $this->getKey($service);

        self::$transactions[$key] = $transaction;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransaction($service)
    {
        $key = $this->getKey($service);

        if ($this->hasTransaction($service)) {
            return self::$transactions[$key];
        }

        throw new TransactionNotFoundException();
    }

    /**
     * {@inheritdoc}
     */
    public function hasTransaction($service)
    {
        $key = $this->getKey($service);

        return array_key_exists($key, self::$transactions);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        self::$transactions = [];

        return true;
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
