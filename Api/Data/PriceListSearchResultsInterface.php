<?php

/**
 * This file is part of the Orangecat PricesList package.
 *
 * (c) Oliverio Gombert <olivertar@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Orangecat\PricesList\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface PriceListSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get price list list.
     *
     * @return \Orangecat\PricesList\Api\Data\PriceListInterface[]
     */
    public function getItems();

    /**
     * Set price list list.
     *
     * @param \Orangecat\PricesList\Api\Data\PriceListInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
