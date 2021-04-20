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

namespace PrestaShop\CircuitBreaker\Util;

/**
 * Util class to handle object validation
 * Should be deprecated for most parts once
 * the library will drop PHP5 support.
 */
final class Assert
{
    /**
     * @param mixed $value the value to evaluate
     */
    public static function isPositiveValue($value): bool
    {
        return !is_string($value) && is_numeric($value) && $value >= 0;
    }

    /**
     * @param mixed $value the value to evaluate
     */
    public static function isPositiveInteger($value): bool
    {
        return self::isPositiveValue($value) && is_int($value);
    }

    /**
     * @param mixed $value the value to evaluate
     */
    public static function isURI($value): bool
    {
        return null !== $value
            && !is_numeric($value)
            && !is_bool($value)
            && false !== filter_var($value, FILTER_SANITIZE_URL)
        ;
    }

    /**
     * @param mixed $value the value to evaluate
     */
    public static function isString($value): bool
    {
        return !empty($value) && is_string($value);
    }
}
