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
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class GetProducts extends Action
{
    public const ADMIN_RESOURCE = 'Orangecat_PricesList::priceslist';

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param ProductRepositoryInterface $productRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        Context $context,
        private readonly JsonFactory $jsonFactory,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        parent::__construct($context);
    }

    /**
     * Return product data for selected IDs as JSON
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->jsonFactory->create();
        $selectedIds = $this->getRequest()->getParam('selected', []);

        if (empty($selectedIds) || !is_array($selectedIds)) {
            return $resultJson->setData(['error' => true, 'message' => 'No products selected.']);
        }

        $criteria = $this->searchCriteriaBuilder
            ->addFilter('entity_id', $selectedIds, 'in')
            ->create();

        $products = $this->productRepository->getList($criteria)->getItems();

        $items = [];
        foreach ($products as $product) {
            $items[] = [
                'id'    => $product->getId(),
                'sku'   => $product->getSku(),
                'name'  => $product->getName(),
                'price' => (float) $product->getPrice()
            ];
        }

        return $resultJson->setData(['error' => false, 'items' => $items]);
    }
}
