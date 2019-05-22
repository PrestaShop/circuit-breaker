<?php

namespace PrestaShop\CircuitBreaker\Places;

use PrestaShop\CircuitBreaker\Contracts\Place;
use PrestaShop\CircuitBreaker\Exceptions\InvalidPlaceException;
use PrestaShop\CircuitBreaker\Utils\Assert;

abstract class AbstractPlace implements Place
{
    /**
     * @var int the Place failures
     */
    private $failures;

    /**
     * @var float the Place timeout
     */
    private $timeout;

    /**
     * @var int the Place threshold
     */
    private $threshold;

    /**
     * @param int $failures the Place failures
     * @param float $timeout the Place timeout
     * @param int $threshold the Place threshold
     */
    public function __construct($failures, $timeout, $threshold)
    {
        $this->validate($failures, $timeout, $threshold);

        $this->failures = $failures;
        $this->timeout = $timeout;
        $this->threshold = $threshold;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function getState();

    /**
     * {@inheritdoc}
     */
    public function getFailures()
    {
        return $this->failures;
    }

    /**
     * {@inheritdoc}
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * {@inheritdoc}
     */
    public function getThreshold()
    {
        return $this->threshold;
    }

    /**
     * Helper: create a Place from an array.
     *
     * @var array the failures, timeout and treshold
     *
     * @return self
     *
     * @throws InvalidPlaceException
     */
    public static function fromArray(array $settings)
    {
        static::validateSettings($settings);

        return new static(
            $settings['failures'],
            $settings['timeout'],
            $settings['threshold']
        );
    }

    /**
     * Ensure the array settings is correctly formatted.
     *
     * @param array $settings
     *
     * @return bool true if valid
     *
     * @throws InvalidPlaceException
     */
    private static function validateSettings(array $settings)
    {
        if (isset($settings['failures']) && isset($settings['timeout']) && isset($settings['threshold'])) {
            return true;
        }

        throw InvalidPlaceException::invalidArraySettings($settings);
    }

    /**
     * Ensure the place is valid (PHP5 is permissive).
     *
     * @param int $failures the failures should be a positive value
     * @param float $timeout the timeout should be a positive value
     * @param int $threshold the threshold should be a positive value
     *
     * @return bool true if valid
     *
     * @throws InvalidPlaceException
     */
    private function validate($failures, $timeout, $threshold)
    {
        $assertionsAreValid = Assert::isPositiveInteger($failures)
            && Assert::isPositiveValue($timeout)
            && Assert::isPositiveInteger($threshold)
        ;

        if ($assertionsAreValid) {
            return true;
        }

        throw InvalidPlaceException::invalidSettings($failures, $timeout, $threshold);
    }
}
