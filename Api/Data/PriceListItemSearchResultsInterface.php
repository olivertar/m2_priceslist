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

interface PriceListItemSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get price list item list.
     *
     * @return \Orangecat\PricesList\Api\Data\PriceListItemInterface[]
     */
    public function getItems();

    /**
     * Set price list item list.
     *
     * @param \Orangecat\PricesList\Api\Data\PriceListItemInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
