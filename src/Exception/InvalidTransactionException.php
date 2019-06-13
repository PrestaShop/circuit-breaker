<?php

namespace PrestaShop\CircuitBreaker\Exception;

use PrestaShop\CircuitBreaker\Util\ErrorFormatter;

final class InvalidTransactionException extends CircuitBreakerException
{
    /**
     * @param mixed $service the service URI
     * @param mixed $failures the failures
     * @param mixed $state the Circuit Breaker
     * @param mixed $threshold the threshold
     *
     * @return self
     */
    public static function invalidParameters($service, $failures, $state, $threshold)
    {
        $exceptionMessage = 'Invalid parameters for Transaction' . PHP_EOL .
            ErrorFormatter::format('service', $service, 'isURI', 'an URI') .
            ErrorFormatter::format('failures', $failures, 'isPositiveInteger', 'a positive integer') .
            ErrorFormatter::format('state', $state, 'isString', 'a string') .
            ErrorFormatter::format('threshold', $threshold, 'isPositiveInteger', 'a positive integer')
        ;

        return new self($exceptionMessage);
    }
}
