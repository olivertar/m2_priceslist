<?php

/**
 * This file is part of the Orangecat PricesList package.
 *
 * (c) Oliverio Gombert <olivertar@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Orangecat\PricesList\Model;

use Orangecat\PricesList\Api\PriceListRepositoryInterface;
use Orangecat\PricesList\Api\Data\PriceListInterface;
use Orangecat\PricesList\Api\Data\PriceListInterfaceFactory;
use Orangecat\PricesList\Api\Data\PriceListSearchResultsInterfaceFactory;
use Orangecat\PricesList\Model\ResourceModel\PriceList as ResourcePriceList;
use Orangecat\PricesList\Model\ResourceModel\PriceList\CollectionFactory as PriceListCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;

class PriceListRepository implements PriceListRepositoryInterface
{
    /**
     * @param ResourcePriceList $resource
     * @param PriceListInterfaceFactory $priceListFactory
     * @param PriceListCollectionFactory $priceListCollectionFactory
     * @param PriceListSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        private readonly ResourcePriceList $resource,
        private readonly PriceListInterfaceFactory $priceListFactory,
        private readonly PriceListCollectionFactory $priceListCollectionFactory,
        private readonly PriceListSearchResultsInterfaceFactory $searchResultsFactory,
        private readonly CollectionProcessorInterface $collectionProcessor
    ) {}

    /**
     * @inheritDoc
     */
    public function save(PriceListInterface $priceList)
    {
        try {
            $this->resource->save($priceList);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the price list: %1',
                $exception->getMessage()
            ));
        }
        return $priceList;
    }

    /**
     * @inheritDoc
     */
    public function getById($entityId)
    {
        $priceList = $this->priceListFactory->create();
        $this->resource->load($priceList, $entityId);
        if (!$priceList->getId()) {
            throw new NoSuchEntityException(__('Price List with id "%1" does not exist.', $entityId));
        }
        return $priceList;
    }

    /**
     * @inheritDoc
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->priceListCollectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function delete(PriceListInterface $priceList)
    {
        try {
            $this->resource->delete($priceList);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the price list: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($entityId)
    {
        return $this->delete($this->getById($entityId));
    }

    /**
     * @inheritDoc
     */
    public function getByCode($code)
    {
        $priceList = $this->priceListFactory->create();
        $this->resource->load($priceList, $code, PriceListInterface::CODE);
        if (!$priceList->getId()) {
            throw new NoSuchEntityException(__('Price List with code "%1" does not exist.', $code));
        }
        return $priceList;
    }

    /**
     * @inheritDoc
     */
    public function deleteByCode($code)
    {
        return $this->delete($this->getByCode($code));
    }
}
