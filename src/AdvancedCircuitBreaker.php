<?php
/**
 * 2007-2019 PrestaShop SA and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
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
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\CircuitBreaker;

use PrestaShop\CircuitBreaker\Contracts\Client;
use PrestaShop\CircuitBreaker\Contracts\Storage;
use PrestaShop\CircuitBreaker\Contracts\System;
use PrestaShop\CircuitBreaker\Contracts\TransitionDispatcher;
use PrestaShop\CircuitBreaker\Exceptions\UnavailableServiceException;

/**
 * This implementation of the CircuitBreaker is a bit more advanced than the SimpleCircuitBreaker,
 * it allows you to setup your client, system, storage and dispatcher.
 */
class AdvancedCircuitBreaker extends PartialCircuitBreaker
{
    /** @var TransitionDispatcher */
    protected $dispatcher;

    /**
     * @param System $system
     * @param Client $client
     * @param Storage $storage
     * @param TransitionDispatcher $dispatcher
     */
    public function __construct(System $system, Client $client, Storage $storage, TransitionDispatcher $dispatcher)
    {
        parent::__construct($system, $client, $storage);
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function call(
        $service,
        callable $fallback,
        array $serviceParameters = []
    ) {
        $transaction = $this->initTransaction($service);

        try {
            if ($this->isOpened()) {
                if (!$this->canAccessService($transaction)) {
                    return call_user_func($fallback);
                }

                $this->moveStateTo(States::HALF_OPEN_STATE, $service);
                $this->dispatchTransition(
                    Transitions::CHECKING_AVAILABILITY_TRANSITION,
                    $service,
                    $serviceParameters
                );
            }

            $response = $this->request($service, $serviceParameters);
            $this->moveStateTo(States::CLOSED_STATE, $service);
            $this->dispatchTransition(
                Transitions::CLOSING_TRANSITION,
                $service,
                $serviceParameters
            );

            return $response;
        } catch (UnavailableServiceException $exception) {
            $transaction->incrementFailures();
            $this->storage->saveTransaction($service, $transaction);
            if (!$this->isAllowedToRetry($transaction)) {
                $this->moveStateTo(States::OPEN_STATE, $service);
                $transition = Transitions::OPENING_TRANSITION;
                if ($this->isHalfOpened()) {
                    $transition = Transitions::REOPENING_TRANSITION;
                }
                $this->dispatchTransition($transition, $service, $serviceParameters);

                return call_user_func($fallback);
            }

            return $this->call(
                $service,
                $fallback,
                $serviceParameters
            );
        }
    }

    /**
     * @param string $transition
     * @param string $service
     * @param array $serviceParameters
     */
    protected function dispatchTransition($transition, $service, array $serviceParameters)
    {
        $this->dispatcher->dispatchTransition($transition, $service, $serviceParameters);
    }

    /**
     * {@inheritdoc}
     */
    protected function initTransaction($service)
    {
        if (!$this->storage->hasTransaction($service)) {
            $this->dispatchTransition(Transitions::INITIATING_TRANSITION, $service, []);
        }

        return parent::initTransaction($service);
    }

    /**
     * {@inheritdoc}
     */
    protected function request($service, array $parameters = [])
    {
        $this->dispatchTransition(Transitions::TRIAL_TRANSITION, $service, $parameters);

        return parent::request($service, $parameters);
    }
}
