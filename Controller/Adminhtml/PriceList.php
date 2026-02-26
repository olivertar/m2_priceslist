<?php

/**
 * This file is part of the Orangecat PricesList package.
 *
 * (c) Oliverio Gombert <olivertar@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Orangecat\PricesList\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;

abstract class PriceList extends Action
{
    public const ADMIN_RESOURCE = 'Orangecat_PricesList::priceslist';

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        protected readonly Registry $coreRegistry
    ) {
        parent::__construct($context);
    }

    /**
     * Init Main Page
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Orangecat_PricesList::priceslist_list')
            ->addBreadcrumb(__('Price Lists'), __('Price Lists'))
            ->addBreadcrumb(__('Manage Price Lists'), __('Manage Price Lists'));
        return $resultPage;
    }
}
