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

namespace PrestaShop\CircuitBreaker\Place;

use PrestaShop\CircuitBreaker\Contract\PlaceInterface;
use PrestaShop\CircuitBreaker\Exception\InvalidPlaceException;
use PrestaShop\CircuitBreaker\Util\Assert;

abstract class AbstractPlace implements PlaceInterface
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
     *
     * @throws InvalidPlaceException
     */
    public function __construct(int $failures, float $timeout, int $threshold)
    {
        $this->validate($failures, $timeout, $threshold);

        $this->failures = $failures;
        $this->timeout = $timeout;
        $this->threshold = $threshold;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function getstate(): string;

    /**
     * {@inheritdoc}
     */
    public function getfailures(): int
    {
        return $this->failures;
    }

    /**
     * {@inheritdoc}
     */
    public function gettimeout(): float
    {
        return $this->timeout;
    }

    /**
     * {@inheritdoc}
     */
    public function getthreshold(): int
    {
        return $this->threshold;
    }

    /**
     * ensure the place is valid (php5 is permissive).
     *
     * @param int $failures the failures should be a positive value
     * @param float $timeout the timeout should be a positive value
     * @param int $threshold the threshold should be a positive value
     *
     * @return bool true if valid
     *
     * @throws invalidplaceexception
     */
    private function validate(int $failures, float $timeout, int $threshold): bool
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
