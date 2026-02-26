<?php

/**
 * This file is part of the Orangecat PricesList package.
 *
 * (c) Oliverio Gombert <olivertar@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Orangecat\PricesList\Controller\Adminhtml\PriceList;

use Orangecat\PricesList\Controller\Adminhtml\PriceList;
use Magento\Framework\Controller\ResultFactory;

class Delete extends PriceList
{
    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $id = $this->getRequest()->getParam('entity_id');
        if ($id) {
            try {
                $model = $this->_objectManager->create(\Orangecat\PricesList\Model\PriceList::class);
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccessMessage(__('You deleted the price list.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['entity_id' => $id]);
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a price list to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}
