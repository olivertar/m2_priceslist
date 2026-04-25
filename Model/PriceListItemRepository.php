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

use Orangecat\PricesList\Api\PriceListItemRepositoryInterface;
use Orangecat\PricesList\Api\Data\PriceListItemInterface;
use Orangecat\PricesList\Api\Data\PriceListItemInterfaceFactory;
use Orangecat\PricesList\Api\Data\PriceListItemSearchResultsInterfaceFactory;
use Orangecat\PricesList\Model\ResourceModel\PriceListItem as ResourcePriceListItem;
use Orangecat\PricesList\Model\ResourceModel\PriceListItem\CollectionFactory as PriceListItemCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;

class PriceListItemRepository implements PriceListItemRepositoryInterface
{
    /**
     * @param ResourcePriceListItem $resource
     * @param PriceListItemInterfaceFactory $priceListItemFactory
     * @param PriceListItemCollectionFactory $priceListItemCollectionFactory
     * @param PriceListItemSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        private readonly ResourcePriceListItem $resource,
        private readonly PriceListItemInterfaceFactory $priceListItemFactory,
        private readonly PriceListItemCollectionFactory $priceListItemCollectionFactory,
        private readonly PriceListItemSearchResultsInterfaceFactory $searchResultsFactory,
        private readonly CollectionProcessorInterface $collectionProcessor
    ) {
    }

    /**
     * @inheritDoc
     */
    public function save(PriceListItemInterface $priceListItem)
    {
        try {
            $this->resource->save($priceListItem);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the price list item: %1',
                $exception->getMessage()
            ));
        }
        return $priceListItem;
    }

    /**
     * @inheritDoc
     */
    public function getById($itemId)
    {
        $priceListItem = $this->priceListItemFactory->create();
        $this->resource->load($priceListItem, $itemId);
        if (!$priceListItem->getId()) {
            throw new NoSuchEntityException(__('Price List Item with id "%1" does not exist.', $itemId));
        }
        return $priceListItem;
    }

    /**
     * @inheritDoc
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->priceListItemCollectionFactory->create();

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
    public function delete(PriceListItemInterface $priceListItem)
    {
        try {
            $this->resource->delete($priceListItem);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the price list item: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($itemId)
    {
        return $this->delete($this->getById($itemId));
    }
}
