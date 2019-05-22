<?php

namespace PrestaShop\CircuitBreaker\Exceptions;

use PrestaShop\CircuitBreaker\Utils\ErrorFormatter;

final class InvalidPlaceException extends CircuitBreakerException
{
    /**
     * @param mixed $failures the failures
     * @param mixed $timeout the timeout
     * @param mixed $threshold the threshold
     *
     * @return self
     */
    public static function invalidSettings($failures, $timeout, $threshold)
    {
        $exceptionMessage = 'Invalid settings for Place' . PHP_EOL .
            ErrorFormatter::format('failures', $failures, 'isPositiveInteger', 'a positive integer') .
            ErrorFormatter::format('timeout', $timeout, 'isPositiveValue', 'a float') .
            ErrorFormatter::format('threshold', $threshold, 'isPositiveInteger', 'a positive integer')
        ;

        return new self($exceptionMessage);
    }

    /**
     * @param array $settings
     *
     * @return InvalidPlaceException
     */
    public static function invalidArraySettings(array $settings)
    {
        $exceptionMessage = 'Invalid settings for Place::fromArray: ' .
            var_export($settings, true) . '. The expected format is:' . PHP_EOL .
            '[\'failures\' => {failures}, \'timeout\' => {timeout}, \'threshold\' => {threshold}]' . PHP_EOL
        ;

        return new self($exceptionMessage);
    }
}
