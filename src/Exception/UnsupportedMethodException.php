<?php

namespace PrestaShop\CircuitBreaker\Exception;

/**
 * Used when trying to use an unsupported HTTP method
 */
class UnsupportedMethodException extends CircuitBreakerException
{
    /**
     * @param string $methodName
     *
     * @return UnsupportedMethodException
     */
    public static function unsupportedMethod($methodName)
    {
        return new static(sprintf('Unsupported method: "%s"', $methodName));
    }
}
