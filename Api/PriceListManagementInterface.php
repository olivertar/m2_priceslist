<?php

/**
 * This file is part of the Orangecat PricesList package.
 *
 * (c) Oliverio Gombert <olivertar@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Orangecat\PricesList\Api;

interface PriceListManagementInterface
{
    /**
     * Get all prices in a price list by its code
     *
     * @param string $priceListCode
     * @return \Orangecat\PricesList\Api\Data\PriceListItemInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPrices($priceListCode);

    /**
     * Add or update prices in a price list
     *
     * @param string $priceListCode
     * @param \Orangecat\PricesList\Api\Data\PriceListItemInterface[] $prices
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addPrices($priceListCode, array $prices);

    /**
     * Remove prices from a price list by product SKUs
     *
     * @param string $priceListCode
     * @param string[] $skus
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function removePrices($priceListCode, array $skus);
}
