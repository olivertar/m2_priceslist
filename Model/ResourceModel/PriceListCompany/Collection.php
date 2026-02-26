<?php

/**
 * This file is part of the Orangecat PricesList package.
 *
 * (c) Oliverio Gombert <olivertar@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Orangecat\PricesList\Model\ResourceModel\PriceListCompany;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Orangecat\PricesList\Model\PriceListCompany as Model;
use Orangecat\PricesList\Model\ResourceModel\PriceListCompany as ResourceModel;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'link_id';

    /**
     * @var string
     */
    protected $_eventPrefix = 'orangecat_priceslist_company_collection';

    /**
     * @var string
     */
    protected $_eventObject = 'company_collection';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }

    /**
     * @inheritdoc
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->joinLeft(
            ['pl' => $this->getTable('priceslist')],
            'main_table.price_list_id = pl.entity_id',
            ['price_list_name' => 'pl.name', 'price_list_code' => 'pl.code']
        );
        return $this;
    }
}
