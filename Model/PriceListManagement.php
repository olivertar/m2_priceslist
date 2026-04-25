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

use Orangecat\PricesList\Api\PriceListManagementInterface;
use Orangecat\PricesList\Api\PriceListRepositoryInterface;
use Orangecat\PricesList\Api\PriceListItemRepositoryInterface;
use Orangecat\PricesList\Api\Data\PriceListItemInterface;
use Orangecat\PricesList\Model\ResourceModel\PriceListItem\CollectionFactory;
use Orangecat\PricesList\Api\Data\PriceListItemInterfaceFactory;

class PriceListManagement implements PriceListManagementInterface
{
    /**
     * @param PriceListRepositoryInterface $priceListRepository
     * @param PriceListItemRepositoryInterface $itemRepository
     * @param CollectionFactory $collectionFactory
     * @param PriceListItemInterfaceFactory $itemFactory
     */
    public function __construct(
        private readonly PriceListRepositoryInterface $priceListRepository,
        private readonly PriceListItemRepositoryInterface $itemRepository,
        private readonly CollectionFactory $collectionFactory,
        private readonly PriceListItemInterfaceFactory $itemFactory
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getPrices($priceListCode)
    {
        $priceList = $this->priceListRepository->getByCode($priceListCode);
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(PriceListItemInterface::PRICE_LIST_ID, $priceList->getId());

        return $collection->getItems();
    }

    /**
     * @inheritDoc
     */
    public function addPrices($priceListCode, array $prices)
    {
        $priceList = $this->priceListRepository->getByCode($priceListCode);
        $priceListId = $priceList->getId();

        foreach ($prices as $itemData) {
            $sku = $itemData->getSku();
            $qty = $itemData->getQty() ?: 1.0;

            // Try to find existing item for this SKU, Price List and QTY
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter(PriceListItemInterface::PRICE_LIST_ID, $priceListId);
            $collection->addFieldToFilter(PriceListItemInterface::SKU, $sku);
            $collection->addFieldToFilter(PriceListItemInterface::QTY, $qty);
            $existingItem = $collection->getFirstItem();

            if ($existingItem->getId()) {
                $item = $this->itemRepository->getById($existingItem->getId());
            } else {
                $item = $this->itemFactory->create();
                $item->setPriceListId($priceListId);
                $item->setSku($sku);
                $item->setQty($qty);
            }

            $item->setDiscountType($itemData->getDiscountType());
            $item->setAmount($itemData->getAmount());

            $this->itemRepository->save($item);
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function removePrices($priceListCode, array $skus)
    {
        $priceList = $this->priceListRepository->getByCode($priceListCode);
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(PriceListItemInterface::PRICE_LIST_ID, $priceList->getId());
        $collection->addFieldToFilter(PriceListItemInterface::SKU, ['in' => $skus]);

        foreach ($collection as $item) {
            $this->itemRepository->delete($item);
        }

        return true;
    }
}
