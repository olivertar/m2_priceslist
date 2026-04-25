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

use Orangecat\PricesList\Api\PriceListCompanyManagementInterface;
use Orangecat\PricesList\Api\PriceListRepositoryInterface;
use Orangecat\PricesList\Api\Data\PriceListCompanyInterface;
use Orangecat\PricesList\Api\Data\PriceListCompanyInterfaceFactory;
use Orangecat\PricesList\Model\ResourceModel\PriceListCompany as ResourcePriceListCompany;
use Orangecat\PricesList\Model\ResourceModel\PriceListCompany\CollectionFactory;

class PriceListCompanyManagement implements PriceListCompanyManagementInterface
{
    /**
     * @param PriceListRepositoryInterface $priceListRepository
     * @param PriceListCompanyInterfaceFactory $linkFactory
     * @param ResourcePriceListCompany $resource
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        private readonly PriceListRepositoryInterface $priceListRepository,
        private readonly PriceListCompanyInterfaceFactory $linkFactory,
        private readonly ResourcePriceListCompany $resource,
        private readonly CollectionFactory $collectionFactory
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getCompanies($priceListCode)
    {
        $priceList = $this->priceListRepository->getByCode($priceListCode);
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(PriceListCompanyInterface::PRICE_LIST_ID, $priceList->getId());

        return $collection->getItems();
    }

    /**
     * @inheritDoc
     */
    public function associate($priceListCode, $companyId, $priority = 0)
    {
        $priceList = $this->priceListRepository->getByCode($priceListCode);
        $priceListId = $priceList->getId();

        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(PriceListCompanyInterface::PRICE_LIST_ID, $priceListId);
        $collection->addFieldToFilter(PriceListCompanyInterface::COMPANY_ID, $companyId);
        $link = $collection->getFirstItem();

        if (!$link->getId()) {
            $link = $this->linkFactory->create();
            $link->setPriceListId($priceListId);
            $link->setCompanyId($companyId);
        }

        $link->setPriority($priority);
        $this->resource->save($link);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function removeAssociation($priceListCode, $companyId)
    {
        $priceList = $this->priceListRepository->getByCode($priceListCode);
        $priceListId = $priceList->getId();

        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(PriceListCompanyInterface::PRICE_LIST_ID, $priceListId);
        $collection->addFieldToFilter(PriceListCompanyInterface::COMPANY_ID, $companyId);
        $link = $collection->getFirstItem();

        if ($link->getId()) {
            $this->resource->delete($link);
        }

        return true;
    }
}
