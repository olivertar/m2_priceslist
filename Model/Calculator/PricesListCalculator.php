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

namespace Orangecat\PricesList\Model\Calculator;

use Orangecat\Prices\Api\PriceCalculatorInterface;
use Orangecat\PricesList\Model\Config;
use Orangecat\PricesList\Model\Config\Source\Mode;
use Orangecat\PricesList\Model\ResourceModel\PriceListCompany\CollectionFactory as CompanyCollectionFactory;
use Orangecat\PricesList\Model\ResourceModel\PriceListItem\CollectionFactory as ItemCollectionFactory;

/**
 * Calculates B2B Prices based on assigned Price Lists.
 */
class PricesListCalculator implements PriceCalculatorInterface
{
    /**
     * @param Config $config
     * @param CompanyCollectionFactory $companyCollectionFactory
     * @param ItemCollectionFactory $itemCollectionFactory
     */
    public function __construct(
        private readonly Config $config,
        private readonly CompanyCollectionFactory $companyCollectionFactory,
        private readonly ItemCollectionFactory $itemCollectionFactory
    ) {
    }

    /**
     * @inheritdoc
     */
    public function calculate(string $sku, float $qty, int $companyId, float $basePrice = 0.0): ?float
    {
        if (!$this->config->isEnabled()) {
            return null; // PricesList module rules are disabled
        }

        // 1. Find all Price Lists assigned to this company, ordered by weight (priority)
        // High priority number = applies first (e.g. 10 defeats 1).
        $listsCollection = $this->companyCollectionFactory->create()
            ->addFieldToFilter('company_id', $companyId)
            ->setOrder('priority', 'DESC');

        if ($listsCollection->getSize() === 0) {
            return null; // Company has no price lists
        }

        $priceListIds = $listsCollection->getColumnValues('price_list_id');
        $resolutionMode = $this->config->getResolutionMode();

        // 2. Fetch the items from those lists matching the requested SKU
        $itemsCollection = $this->itemCollectionFactory->create()
            ->addFieldToFilter('price_list_id', ['in' => $priceListIds])
            ->addFieldToFilter('sku', $sku);

        if ($itemsCollection->getSize() === 0) {
            return null; // The product is not in any of the company's price lists
        }

        // We group items by their price_list_id to process them according to the list's priority
        $itemsByListId = [];
        foreach ($itemsCollection as $item) {
            $itemsByListId[(int)$item->getPriceListId()] = $item;
        }

        $finalPrice = null;

        // 3. Resolve the price based on Mode (Cascade vs Weight)
        // Iterate through all assigned lists in priority descending order.
        foreach ($priceListIds as $listId) {
            if (!isset($itemsByListId[(int)$listId])) {
                continue; // This list doesn't contain the SKU
            }

            $listPriceItem = $itemsByListId[(int)$listId];

            // Validate minimum Qty requirements (Volume Pricing Tier)
            if ($qty < (float)$listPriceItem->getQty()) {
                continue; // The customer is not buying enough to unlock this price
            }

            $amount = (float)$listPriceItem->getAmount();
            $discountType = $listPriceItem->getDiscountType();
            $currentPrice = null;

            if ($discountType === 'fixed_price') {
                $currentPrice = $amount;
            } elseif ($discountType === 'percent' || $discountType === 'percentage') {
                $currentPrice = $basePrice - ($basePrice * ($amount / 100));
            } elseif ($discountType === 'discount' || $discountType === 'fixed_discount') {
                $currentPrice = $basePrice - $amount;
            } else {
                continue;
            }

            // Boundary safety
            $currentPrice = max(0, $currentPrice);

            if ($resolutionMode === Mode::MODE_CASCADE) {
                // Cascade: The first list that has the item wins, because they are ordered by DESC priority.
                return $currentPrice;
            } elseif ($resolutionMode === Mode::MODE_WEIGHT) {
                // Weight: The lowest configured price across ALL valid lists wins.
                if ($finalPrice === null || $currentPrice < $finalPrice) {
                    $finalPrice = $currentPrice;
                }
            }
        }

        return $finalPrice;
    }

    /**
     * @inheritdoc
     */
    public function getTiers(string $sku, int $companyId, float $basePrice = 0.0): array
    {
        if (!$this->config->isEnabled()) {
            return [];
        }

        $listsCollection = $this->companyCollectionFactory->create()
            ->addFieldToFilter('company_id', $companyId)
            ->setOrder('priority', 'DESC');

        if ($listsCollection->getSize() === 0) {
            return [];
        }

        $priceListIds = $listsCollection->getColumnValues('price_list_id');
        $resolutionMode = $this->config->getResolutionMode();

        $itemsCollection = $this->itemCollectionFactory->create()
            ->addFieldToFilter('price_list_id', ['in' => $priceListIds])
            ->addFieldToFilter('sku', $sku);

        if ($itemsCollection->getSize() === 0) {
            return [];
        }

        // We group items by their price_list_id
        $itemsByListId = [];
        foreach ($itemsCollection as $item) {
            $listId = (int)$item->getPriceListId();
            if (!isset($itemsByListId[$listId])) {
                $itemsByListId[$listId] = [];
            }
            $itemsByListId[$listId][] = $item;
        }

        $allTiers = [];

        foreach ($priceListIds as $listId) {
            if (!isset($itemsByListId[(int)$listId])) {
                continue;
            }

            foreach ($itemsByListId[(int)$listId] as $listPriceItem) {
                $qty = (float)$listPriceItem->getQty();
                if ($qty <= 1.0) {
                    continue;
                }

                $amount = (float)$listPriceItem->getAmount();
                $discountType = $listPriceItem->getDiscountType();
                $currentPrice = null;

                if ($discountType === 'fixed_price') {
                    $currentPrice = $amount;
                } elseif ($discountType === 'percent' || $discountType === 'percentage') {
                    $currentPrice = $basePrice - ($basePrice * ($amount / 100));
                } elseif ($discountType === 'discount' || $discountType === 'fixed_discount') {
                    $currentPrice = $basePrice - $amount;
                } else {
                    continue;
                }

                $currentPrice = max(0, $currentPrice);

                if (!isset($allTiers[(string)$qty])) {
                    $allTiers[(string)$qty] = $currentPrice;
                } else {
                    if ($resolutionMode === Mode::MODE_WEIGHT) {
                        if ($currentPrice < $allTiers[(string)$qty]) {
                            $allTiers[(string)$qty] = $currentPrice;
                        }
                    }
                    // If Mode::MODE_CASCADE, the first list (highest priority) wins, so we do NOT overwrite
                }
            }
        }

        $tiers = [];
        foreach ($allTiers as $qtyStr => $price) {
            $tiers[] = [
                'qty' => (float)$qtyStr,
                'price' => $price
            ];
        }

        // Sort by qty ascending
        usort($tiers, function ($a, $b) {
            return $a['qty'] <=> $b['qty'];
        });

        return $tiers;
    }
}
