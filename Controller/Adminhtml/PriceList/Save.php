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

namespace Orangecat\PricesList\Controller\Adminhtml\PriceList;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session as BackendSession;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Orangecat\PricesList\Controller\Adminhtml\PriceList;
use Orangecat\PricesList\Model\Config\Source\DiscountType;
use Orangecat\PricesList\Model\PriceListFactory;
use Orangecat\PricesList\Model\PriceListItemFactory;
use Orangecat\PricesList\Model\PriceListItemRepository;
use Psr\Log\LoggerInterface;

class Save extends PriceList
{
    private const ALLOWED_FIELDS = [
        'name', 'code', 'is_active', 'description', 'start_date', 'end_date',
    ];

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PriceListFactory $priceListFactory
     * @param PriceListItemFactory $itemFactory
     * @param PriceListItemRepository $itemRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param BackendSession $backendSession
     * @param FormKeyValidator $formKeyValidator
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        private readonly PriceListFactory $priceListFactory,
        private readonly PriceListItemFactory $itemFactory,
        private readonly PriceListItemRepository $itemRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly BackendSession $backendSession,
        private readonly FormKeyValidator $formKeyValidator,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct($context, $coreRegistry);
    }

    /**
     * @inheritdoc
     */
    public function execute(): ResultInterface
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage(__('Invalid form key. Please refresh the page.'));
            return $resultRedirect->setPath('*/*/');
        }

        $data = $this->getRequest()->getPostValue();

        if (!$data) {
            return $resultRedirect->setPath('*/*/');
        }

        $id    = $this->getRequest()->getParam('entity_id');
        $model = $this->priceListFactory->create();

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This price list no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
        }

        $filtered = array_intersect_key($data, array_flip(self::ALLOWED_FIELDS));
        $model->addData($filtered);

        try {
            $model->save();
            $this->messageManager->addSuccessMessage(__('You saved the price list.'));

            if (isset($data['product_listing']) && is_array($data['product_listing'])) {
                $this->saveProductListingItems((int)$model->getId(), $data['product_listing']);
            }

            $this->backendSession->setFormData(false);

            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', ['entity_id' => $model->getId()]);
            }

            return $resultRedirect->setPath('*/*/');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error('PricesList save failed: ' . $e->getMessage());
            $this->messageManager->addErrorMessage(__('Something went wrong while saving the price list.'));
        }

        $this->backendSession->setFormData($data);
        return $resultRedirect->setPath('*/*/edit', ['entity_id' => $this->getRequest()->getParam('entity_id')]);
    }

    /**
     * Saves product listing items for a price list, skipping duplicates.
     *
     * @param int $priceListId
     * @param array $products
     */
    private function saveProductListingItems(int $priceListId, array $products): void
    {
        foreach ($products as $productData) {
            if (empty($productData['sku'])) {
                continue;
            }

            $existing = $this->itemRepository->getList(
                $this->searchCriteriaBuilder
                    ->addFilter('price_list_id', $priceListId)
                    ->addFilter('sku', $productData['sku'])
                    ->create()
            );

            if ($existing->getTotalCount() > 0) {
                continue;
            }

            $price   = isset($productData['price']) ? (float)$productData['price'] : 0.0;
            $newItem = $this->itemFactory->create();
            $newItem->setPriceListId($priceListId);
            $newItem->setSku($productData['sku']);
            $newItem->setDiscountType(DiscountType::FIXED_PRICE);
            $newItem->setAmount($price);
            $newItem->setQty(1);
            $this->itemRepository->save($newItem);
        }
    }
}
