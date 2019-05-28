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

namespace Tests\PrestaShop\CircuitBreaker;

use PHPUnit\Framework\TestCase;
use PrestaShop\CircuitBreaker\FactorySettings;

class FactorySettingsTest extends TestCase
{
    public function testSimpleSettings()
    {
        $settings = new FactorySettings(2, 0.5, 10);
        $this->assertNotNull($settings);
        $this->assertEquals(2, $settings->getFailures());
        $this->assertEquals(0.5, $settings->getTimeout());
        $this->assertEquals(10, $settings->getThreshold());
        $this->assertEquals(2, $settings->getStrippedFailures());
        $this->assertEquals(0.5, $settings->getStrippedTimeout());
    }

    public function testMergeSettings()
    {
        $defaultSettings = new FactorySettings(2, 0.5, 10);
        $defaultSettings
            ->setStrippedTimeout(1.2)
            ->setStrippedFailures(1)
        ;

        $this->assertEquals(2, $defaultSettings->getFailures());
        $this->assertEquals(0.5, $defaultSettings->getTimeout());
        $this->assertEquals(10, $defaultSettings->getThreshold());
        $this->assertEquals(1, $defaultSettings->getStrippedFailures());
        $this->assertEquals(1.2, $defaultSettings->getStrippedTimeout());

        $settings = new FactorySettings(2, 1.5, 20);

        $mergedSettings = FactorySettings::merge($defaultSettings, $settings);
        $this->assertEquals(2, $mergedSettings->getFailures());
        $this->assertEquals(1.5, $mergedSettings->getTimeout());
        $this->assertEquals(20, $mergedSettings->getThreshold());
        $this->assertEquals(2, $mergedSettings->getStrippedFailures());
        $this->assertEquals(1.5, $mergedSettings->getStrippedTimeout());
    }
}
