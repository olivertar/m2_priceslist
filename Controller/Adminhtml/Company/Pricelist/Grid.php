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

namespace Orangecat\PricesList\Controller\Adminhtml\Company\Pricelist;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\View\LayoutFactory;

/**
 * AJAX controller that re-renders the PriceListGrid block.
 * Called on search / reset-filter in the company form "Price Lists" tab.
 *
 * Mirrors the pattern used by
 * Orangecat\Company\Controller\Adminhtml\Company\CustomerGrid.
 */
class Grid extends Action
{
    public const ADMIN_RESOURCE = 'Orangecat_Company::company_save';

    /**
     * @param Context $context
     * @param RawFactory $resultRawFactory
     * @param LayoutFactory $layoutFactory
     */
    public function __construct(
        Context $context,
        private readonly RawFactory $resultRawFactory,
        private readonly LayoutFactory $layoutFactory
    ) {
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $layout    = $this->layoutFactory->create();
        $gridBlock = $layout->createBlock(
            \Orangecat\PricesList\Block\Adminhtml\Company\Edit\Tab\PriceListGrid::class,
            'company.pricelist.grid'
        );

        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setContents($gridBlock->toHtml());
        return $resultRaw;
    }
}
