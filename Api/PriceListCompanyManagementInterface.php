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

interface PriceListCompanyManagementInterface
{
    /**
     * Get all companies associated with a price list by its code
     *
     * @param string $priceListCode
     * @return \Orangecat\PricesList\Api\Data\PriceListCompanyInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCompanies($priceListCode);

    /**
     * Associate a price list with a company or update its priority
     *
     * @param string $priceListCode
     * @param int $companyId
     * @param int $priority
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function associate($priceListCode, $companyId, $priority = 0);

    /**
     * Remove association between a price list and a company
     *
     * @param string $priceListCode
     * @param int $companyId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function removeAssociation($priceListCode, $companyId);
}
