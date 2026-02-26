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

use Magento\Framework\Api\SearchCriteriaInterface;
use Orangecat\PricesList\Api\Data\PriceListItemInterface;

interface PriceListItemRepositoryInterface
{
    /**
     * Save PriceListItem
     *
     * @param \Orangecat\PricesList\Api\Data\PriceListItemInterface $priceListItem
     * @return \Orangecat\PricesList\Api\Data\PriceListItemInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(PriceListItemInterface $priceListItem);

    /**
     * Retrieve PriceListItem
     *
     * @param int $itemId
     * @return \Orangecat\PricesList\Api\Data\PriceListItemInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($itemId);

    /**
     * Retrieve PriceListItem matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Orangecat\PricesList\Api\Data\PriceListItemSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete PriceListItem
     *
     * @param \Orangecat\PricesList\Api\Data\PriceListItemInterface $priceListItem
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(PriceListItemInterface $priceListItem);

    /**
     * Delete PriceListItem by ID
     *
     * @param int $itemId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($itemId);
}
