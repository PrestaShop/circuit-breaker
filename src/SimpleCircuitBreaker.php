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

namespace PrestaShop\CircuitBreaker;

use PrestaShop\CircuitBreaker\Contract\ClientInterface;
use PrestaShop\CircuitBreaker\Contract\PlaceInterface;
use PrestaShop\CircuitBreaker\Exception\UnavailableServiceException;
use PrestaShop\CircuitBreaker\Storage\SimpleArray;
use PrestaShop\CircuitBreaker\System\MainSystem;

/**
 * Main implementation of Circuit Breaker.
 */
final class SimpleCircuitBreaker extends PartialCircuitBreaker
{
    public function __construct(
        PlaceInterface $openPlace,
        PlaceInterface $halfOpenPlace,
        PlaceInterface $closedPlace,
        ClientInterface $client
    ) {
        $system = new MainSystem($closedPlace, $halfOpenPlace, $openPlace);

        parent::__construct($system, $client, new SimpleArray());
    }

    /**
     * {@inheritdoc}
     */
    public function call(
        string $service,
        array $serviceParameters = [],
        callable $fallback = null
    ): string {
        $transaction = $this->initTransaction($service);
        try {
            if ($this->isOpened()) {
                if (!$this->canAccessService($transaction)) {
                    return $this->callFallback($fallback);
                }

                $this->moveStateTo(State::HALF_OPEN_STATE, $service);
            }
            $response = $this->request($service, $serviceParameters);
            $this->moveStateTo(State::CLOSED_STATE, $service);

            return $response;
        } catch (UnavailableServiceException $exception) {
            $transaction->incrementFailures();
            $this->storage->saveTransaction($service, $transaction);
            if (!$this->isAllowedToRetry($transaction)) {
                $this->moveStateTo(State::OPEN_STATE, $service);

                return $this->callFallback($fallback);
            }

            return $this->call($service, $serviceParameters, $fallback);
        }
    }
}
