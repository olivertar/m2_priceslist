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

class InlineEdit extends Action
{
    public const ADMIN_RESOURCE = 'Orangecat_PricesList::priceslist';

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        private readonly JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
    }

    /**
     * Inline edit action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        if ($this->getRequest()->getParam('isAjax')) {
            $postItems = $this->getRequest()->getParam('items', []);
            if (!count($postItems)) {
                $messages[] = __('Please correct the data sent.');
                $error = true;
            } else {
                $allowedFields = ['discount_type', 'amount', 'qty'];
                foreach (array_keys($postItems) as $id) {
                    /** @var \Orangecat\PricesList\Model\PriceListItem $model */
                    $model = $this->_objectManager->create(\Orangecat\PricesList\Model\PriceListItem::class)->load($id);
                    try {
                        $model->addData(array_intersect_key($postItems[$id], array_flip($allowedFields)));
                        $model->save();
                    } catch (LocalizedException $e) {
                        $messages[] = $this->getErrorWithItemId($model, $e->getMessage());
                        $error = true;
                    } catch (\Exception $e) {
                        $messages[] = $this->getErrorWithItemId(
                            $model,
                            __('Something went wrong while saving the item.')
                        );
                        $error = true;
                    }
                }
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * Add item id to error message
     *
     * @param \Orangecat\PricesList\Model\PriceListItem $item
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithItemId($item, $errorText)
    {
        return '[Item ID: ' . $item->getId() . '] ' . $errorText;
    }
}
