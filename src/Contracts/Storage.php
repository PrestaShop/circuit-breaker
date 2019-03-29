<?php

namespace PrestaShop\CircuitBreaker\Contracts;

use PrestaShop\CircuitBreaker\Exceptions\TransactionNotFoundException;

/**
 * Store the transaction between the Circuit Breaker
 * and the tiers service.
 */
interface Storage
{
    /**
     * Save the CircuitBreaker transaction.
     *
     * @var string The service name
     * @var Transaction $transaction the transaction
     *
     * @return bool
     *
     * @param mixed $service
     */
    public function saveTransaction($service, Transaction $transaction);

    /**
     * Retrieve the CircuitBreaker transaction for a specific service.
     *
     * @param string the service name
     *
     * @return Transaction
     *
     * @throws TransactionNotFoundException
     */
    public function getTransaction($service);

    /**
     * Checks if the transaction exists.
     *
     * @var string the service name
     *
     * @return bool
     *
     * @param mixed $service
     */
    public function hasTransaction($service);

    /**
     * Clear the Circuit Breaker storage.
     *
     * @return bool
     */
    public function clear();
}
