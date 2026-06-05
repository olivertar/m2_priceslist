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

namespace Orangecat\PricesList\Controller\Adminhtml\PriceList\Item;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Orangecat\PricesList\Model\PriceListItemFactory;
use Orangecat\PricesList\Model\PriceListItemRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Orangecat\PricesList\Model\Config\Source\DiscountType;

class Add extends Action
{
    public const ADMIN_RESOURCE = 'Orangecat_PricesList::priceslist';

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param PriceListItemFactory $itemFactory
     * @param PriceListItemRepository $itemRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        Context $context,
        private readonly JsonFactory $jsonFactory,
        private readonly PriceListItemFactory $itemFactory,
        private readonly PriceListItemRepository $itemRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        parent::__construct($context);
    }

    /**
     * Add items action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();

        $priceListId = $this->getRequest()->getParam('price_list_id');
        // Accepts either a flat `selected` array (legacy) or a `products` array with per-product pricing
        $productsParam = $this->getRequest()->getParam('products', []);
        $selectedIds   = $this->getRequest()->getParam('selected', []);

        if (!$priceListId) {
            return $resultJson->setData([
                'error'   => true,
                'message' => __('Price List ID is missing.')
            ]);
        }

        // Build a map of product data from the `products` param if present
        $validDiscountTypes = [DiscountType::FIXED_PRICE, DiscountType::FIXED_AMOUNT, DiscountType::PERCENTAGE];
        $productDataMap = [];
        if (!empty($productsParam) && is_array($productsParam)) {
            foreach ($productsParam as $productEntry) {
                if (!empty($productEntry['sku'])) {
                    $discountType = $productEntry['discount_type'] ?? DiscountType::FIXED_PRICE;
                    if (!in_array($discountType, $validDiscountTypes, true)) {
                        $discountType = DiscountType::FIXED_PRICE;
                    }
                    $amount = isset($productEntry['amount']) ? (float)$productEntry['amount'] : 0;
                    $productDataMap[$productEntry['sku']] = [
                        'discount_type' => $discountType,
                        'amount'        => max(0.0, $amount),
                        'qty'           => isset($productEntry['qty']) ? max(0.0, (float)$productEntry['qty']) : 1.0,
                    ];
                }
            }
        }

        /** @var \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder */
        $criteriaBuilder = $this->_objectManager->create(\Magento\Framework\Api\SearchCriteriaBuilder::class);

        // If products param provided, fetch by SKU, otherwise fetch by entity IDs
        if (!empty($productDataMap)) {
            $skus = array_keys($productDataMap);
            $searchCriteria = $criteriaBuilder->addFilter('sku', $skus, 'in')->create();
        } elseif (!empty($selectedIds)) {
            $searchCriteria = $criteriaBuilder->addFilter('entity_id', $selectedIds, 'in')->create();
        } else {
            return $resultJson->setData(['error' => false, 'message' => __('No items selected.')]);
        }

        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $this->_objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $products = $productRepository->getList($searchCriteria)->getItems();

        $count = 0;
        foreach ($products as $product) {
            try {
                $pData = isset($productDataMap[$product->getSku()])
                    ? $productDataMap[$product->getSku()]
                    : [
                        'discount_type' => DiscountType::FIXED_PRICE,
                        'amount'        => (float)$product->getPrice(),
                        'qty'           => 1.0,
                    ];

                $existingCriteria = $this->_objectManager->create(\Magento\Framework\Api\SearchCriteriaBuilder::class)
                    ->addFilter('price_list_id', $priceListId)
                    ->addFilter('sku', $product->getSku())
                    ->addFilter('qty', $pData['qty'])
                    ->create();
                $existing = $this->itemRepository->getList($existingCriteria);
                $existingItems = $existing->getItems();

                if (count($existingItems) > 0) {
                    $item = reset($existingItems);
                } else {
                    $item = $this->itemFactory->create();
                    $item->setPriceListId($priceListId);
                    $item->setSku($product->getSku());
                    $item->setQty($pData['qty']);
                }

                $item->setDiscountType($pData['discount_type']);
                $item->setAmount($pData['amount']);
                $this->itemRepository->save($item);
                $count++;
            } catch (\Exception $e) {
                unset($e); // Continue with other products
            }
        }

        return $resultJson->setData([
            'error'   => false,
            'message' => __('%1 items added.', $count)
        ]);
    }
}
