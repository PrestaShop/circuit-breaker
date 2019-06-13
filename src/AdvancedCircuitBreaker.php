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

use PrestaShop\CircuitBreaker\Contract\ClientInterface;
use PrestaShop\CircuitBreaker\Contract\StorageInterface;
use PrestaShop\CircuitBreaker\Contract\SystemInterface;
use PrestaShop\CircuitBreaker\Contract\TransitionDispatcherInterface;
use PrestaShop\CircuitBreaker\Exception\UnavailableServiceException;

/**
 * This implementation of the CircuitBreaker is a bit more advanced than the SimpleCircuitBreaker,
 * it allows you to setup your client, system, storage and dispatcher.
 */
class AdvancedCircuitBreaker extends PartialCircuitBreaker
{
    /** @var TransitionDispatcherInterface */
    protected $dispatcher;

    /** @var callable|null */
    protected $defaultFallback;

    /**
     * @param SystemInterface $system
     * @param ClientInterface $client
     * @param StorageInterface $storage
     * @param TransitionDispatcherInterface $dispatcher
     */
    public function __construct(
        SystemInterface $system,
        ClientInterface $client,
        StorageInterface $storage,
        TransitionDispatcherInterface $dispatcher
    ) {
        parent::__construct($system, $client, $storage);
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function call(
        $service,
        array $serviceParameters = [],
        callable $fallback = null
    ) {
        $transaction = $this->initTransaction($service);

        try {
            if ($this->isOpened()) {
                if (!$this->canAccessService($transaction)) {
                    return $this->callFallback($fallback);
                }

                $this->moveStateTo(State::HALF_OPEN_STATE, $service);
                $this->dispatchTransition(
                    Transition::CHECKING_AVAILABILITY_TRANSITION,
                    $service,
                    $serviceParameters
                );
            }

            $response = $this->request($service, $serviceParameters);
            $this->moveStateTo(State::CLOSED_STATE, $service);
            $this->dispatchTransition(
                Transition::CLOSING_TRANSITION,
                $service,
                $serviceParameters
            );

            return $response;
        } catch (UnavailableServiceException $exception) {
            $transaction->incrementFailures();
            $this->storage->saveTransaction($service, $transaction);
            if (!$this->isAllowedToRetry($transaction)) {
                $this->moveStateTo(State::OPEN_STATE, $service);
                $transition = $this->isHalfOpened() ? Transition::REOPENING_TRANSITION : Transition::OPENING_TRANSITION;
                $this->dispatchTransition($transition, $service, $serviceParameters);

                return $this->callFallback($fallback);
            }

            return $this->call(
                $service,
                $serviceParameters,
                $fallback
            );
        }
    }

    /**
     * @return callable|null
     */
    public function getDefaultFallback()
    {
        return $this->defaultFallback;
    }

    /**
     * @param callable $defaultFallback|null
     *
     * @return AdvancedCircuitBreaker
     */
    public function setDefaultFallback(callable $defaultFallback = null)
    {
        $this->defaultFallback = $defaultFallback;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function callFallback(callable $fallback = null)
    {
        return parent::callFallback(null !== $fallback ? $fallback : $this->defaultFallback);
    }

    /**
     * @param string $transition
     * @param string $service
     * @param array $serviceParameters
     *
     * @return void
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
            $this->dispatchTransition(Transition::INITIATING_TRANSITION, $service, []);
        }

        return parent::initTransaction($service);
    }

    /**
     * {@inheritdoc}
     */
    protected function request($service, array $parameters = [])
    {
        $this->dispatchTransition(Transition::TRIAL_TRANSITION, $service, $parameters);

        return parent::request($service, $parameters);
    }
}
