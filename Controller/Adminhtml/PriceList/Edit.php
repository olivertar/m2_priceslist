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

class Edit extends PriceList
{
    /**
     * Edit action
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('entity_id');
        $model = $this->_objectManager->create(\Orangecat\PricesList\Model\PriceList::class);

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This price list no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                return $resultRedirect->setPath('*/*/');
            }
        }

        $this->coreRegistry->register('orangecat_priceslist_pricelist', $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $this->initPage($resultPage);
        $resultPage->addBreadcrumb(
            $id ? __('Edit Price List') : __('New Price List'),
            $id ? __('Edit Price List') : __('New Price List')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Price Lists'));
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? $model->getName() : __('New Price List'));

        return $resultPage;
    }
}
