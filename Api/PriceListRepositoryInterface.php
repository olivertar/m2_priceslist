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
use Orangecat\PricesList\Api\Data\PriceListInterface;

interface PriceListRepositoryInterface
{
    /**
     * Save PriceList
     *
     * @param \Orangecat\PricesList\Api\Data\PriceListInterface $priceList
     * @return \Orangecat\PricesList\Api\Data\PriceListInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(PriceListInterface $priceList);

    /**
     * Retrieve PriceList
     *
     * @param int $entityId
     * @return \Orangecat\PricesList\Api\Data\PriceListInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($entityId);

    /**
     * Retrieve PriceList matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Orangecat\PricesList\Api\Data\PriceListSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete PriceList
     *
     * @param \Orangecat\PricesList\Api\Data\PriceListInterface $priceList
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(PriceListInterface $priceList);

    /**
     * Delete PriceList by ID
     *
     * @param int $entityId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($entityId);

    /**
     * Retrieve PriceList by Code
     *
     * @param string $code
     * @return \Orangecat\PricesList\Api\Data\PriceListInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByCode($code);

    /**
     * Delete PriceList by Code
     *
     * @param string $code
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteByCode($code);
}
