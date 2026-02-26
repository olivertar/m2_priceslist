<?php

/**
 * This file is part of the Orangecat PricesList package.
 *
 * (c) Oliverio Gombert <olivertar@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Orangecat\PricesList\Model\ResourceModel\PriceList;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Orangecat\PricesList\Model\PriceList as Model;
use Orangecat\PricesList\Model\ResourceModel\PriceList as ResourceModel;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * @var string
     */
    protected $_eventPrefix = 'orangecat_priceslist_pricelist_collection';

    /**
     * @var string
     */
    protected $_eventObject = 'pricelist_collection';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
