<?php

/**
 * This file is part of the Orangecat PricesList package.
 *
 * (c) Oliverio Gombert <olivertar@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Orangecat\PricesList\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Orangecat\PricesList\Model\Config\Source\Mode;

/**
 * Provides access to the system configuration settings for the PricesList module.
 */
class Config
{
    /**
     * XML Path to the enable price lists setting
     */
    public const XML_PATH_ENABLED = 'prices/priceslist/enabled';

    /**
     * XML Path to the price list resolution mode setting
     */
    public const XML_PATH_MODE = 'prices/priceslist/mode';

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * Check if custom B2B Price Lists are enabled.
     *
     * @param int|string|null $storeId
     * @return bool
     */
    public function isEnabled($storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get the configured resolution mode when a customer has multiple price lists.
     *
     * Returns 'cascade' or 'weight' (Mode::MODE_CASCADE or Mode::MODE_WEIGHT).
     *
     * @param int|string|null $storeId
     * @return string
     */
    public function getResolutionMode($storeId = null): string
    {
        $mode = $this->scopeConfig->getValue(
            self::XML_PATH_MODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return $mode ?: Mode::MODE_CASCADE;
    }
}
