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

namespace Orangecat\PricesList\Block\Adminhtml\Company\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Orangecat\PricesList\Block\Adminhtml\Company\Edit\Tab\Renderer\Checkbox as CheckboxRenderer;
use Orangecat\PricesList\Block\Adminhtml\Company\Edit\Tab\Renderer\PriorityInput as PriorityInputRenderer;
use Orangecat\PricesList\Model\ResourceModel\PriceList\CollectionFactory as PriceListCollectionFactory;
use Orangecat\PricesList\Model\ResourceModel\PriceListCompany\CollectionFactory as LinkCollectionFactory;

/**
 * Grid block for assigning Price Lists to a Company.
 * Renders within the Company edit form (htmlContent tab).
 * Uses checkboxes (not radio) to allow multiple selections.
 * On initial load shows only assigned lists; after Reset Filter shows all.
 */
class PriceListGrid extends Extended
{
    /**
     * @param Context $context
     * @param Data $backendHelper
     * @param PriceListCollectionFactory $priceListCollectionFactory
     * @param LinkCollectionFactory $linkCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        private readonly PriceListCollectionFactory $priceListCollectionFactory,
        private readonly LinkCollectionFactory $linkCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('company_pricelist_grid');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareCollection()
    {
        $collection = $this->priceListCollectionFactory->create();

        // On initial page load (non-Ajax): show only the assigned lists.
        // On Ajax (search/reset): show all (standard behavior).
        if (!$this->getRequest()->getParam('isAjax')) {
            $assignedIds = $this->getAssignedPriceListIds();
            if (!empty($assignedIds)) {
                $collection->addFieldToFilter('entity_id', ['in' => $assignedIds]);
            } else {
                // Nothing assigned yet — show nothing on initial load
                $collection->addFieldToFilter('entity_id', -1);
            }
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @inheritdoc
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_company_pricelist',
            [
                'type'             => 'checkbox',
                'html_name'        => 'price_list_ids[]',
                'align'            => 'center',
                'index'            => 'entity_id',
                'header_css_class' => 'col-select',
                'column_css_class' => 'col-select',
                'values'           => $this->getAssignedPriceListIds(),
                'width'            => '50px',
                'renderer'         => CheckboxRenderer::class,
            ]
        );

        $this->addColumn(
            'entity_id',
            [
                'header'           => __('ID'),
                'sortable'         => true,
                'index'            => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );

        $this->addColumn(
            'name',
            [
                'header' => __('Price List Name'),
                'index'  => 'name',
            ]
        );

        $this->addColumn(
            'code',
            [
                'header' => __('Code'),
                'index'  => 'code',
            ]
        );

        $this->addColumn(
            'is_active',
            [
                'header'  => __('Status'),
                'index'   => 'is_active',
                'type'    => 'options',
                'options' => [
                    1 => __('Active'),
                    0 => __('Inactive'),
                ],
            ]
        );

        $this->addColumn(
            'priority',
            [
                'header'           => __('Priority'),
                'index'            => 'entity_id',
                'align'            => 'center',
                'filter'           => false,
                'sortable'         => false,
                'width'            => '80px',
                'renderer'         => PriorityInputRenderer::class,
                'assigned_values'  => $this->getAssignedPriceListData(),
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Grid AJAX reload URL
     */
    public function getGridUrl(): string
    {
        $companyId = (int)$this->getRequest()->getParam('entity_id');
        return $this->getUrl('orangecat_priceslist/company_pricelist/grid', ['entity_id' => $companyId]);
    }

    /**
     * Returns array of currently assigned price list IDs for this company
     *
     * @return int[]
     */
    public function getAssignedPriceListIds(): array
    {
        return array_column($this->getAssignedPriceListData(), 'price_list_id');
    }

    /**
     * Returns all priceslist_company rows for the current company
     *
     * Example format: [['price_list_id' => X, 'priority' => Y], ...]
     *
     * @return array
     */
    public function getAssignedPriceListData(): array
    {
        static $data;
        if ($data !== null) {
            return $data;
        }

        $companyId = (int)$this->getRequest()->getParam('entity_id');
        if (!$companyId) {
            return $data = [];
        }

        $collection = $this->linkCollectionFactory->create();
        $collection->addFieldToFilter('company_id', $companyId);

        $data = [];
        foreach ($collection as $link) {
            $data[] = [
                'price_list_id' => (int)$link->getPriceListId(),
                'priority'      => (int)$link->getPriority(),
            ];
        }

        return $data;
    }
}
