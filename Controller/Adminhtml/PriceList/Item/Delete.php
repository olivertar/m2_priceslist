<?php

/**
 * This file is part of the Orangecat PricesList package.
 *
 * (c) Oliverio Gombert <olivertar@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Orangecat\PricesList\Controller\Adminhtml\PriceList\Item;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Delete extends Action
{
    public const ADMIN_RESOURCE = 'Orangecat_PricesList::priceslist';

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $id = $this->getRequest()->getParam('item_id');
        if ($id) {
            try {
                $model = $this->_objectManager->create(\Orangecat\PricesList\Model\PriceListItem::class);
                $model->load($id);
                $priceListId = $model->getPriceListId();
                $model->delete();
                $this->messageManager->addSuccessMessage(__('You deleted the item.'));

                if ($priceListId) {
                    return $resultRedirect->setPath(
                        'orangecat_priceslist/pricelist/edit',
                        ['entity_id' => $priceListId]
                    );
                }
                return $resultRedirect->setPath('orangecat_priceslist/pricelist/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('orangecat_priceslist/pricelist/');
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find an item to delete.'));
        return $resultRedirect->setPath('orangecat_priceslist/pricelist/');
    }
}
