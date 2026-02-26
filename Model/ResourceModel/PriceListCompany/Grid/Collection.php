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

namespace Orangecat\PricesList\Model\ResourceModel\PriceListCompany\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Orangecat\PricesList\Model\PriceListCompany;
use Orangecat\PricesList\Model\ResourceModel\PriceListCompany as ResourceModel;
use Psr\Log\LoggerInterface;

/**
 * Grid collection for the Companies listing inside the Price List edit form.
 * Joins mycompany to expose the company name as a column.
 */
class Collection extends SearchResult
{
    /**
     * @var string
     */
    protected $model = PriceListCompany::class;

    /**
     * @var string
     */
    protected $resourceModel = ResourceModel::class;

    /**
     * @inheritdoc
     */
    protected function _initSelect(): static
    {
        parent::_initSelect();

        $this->getSelect()->joinLeft(
            ['name' => $this->getTable('mycompany')],
            'main_table.company_id = name.entity_id',
            ['name' => 'name.name']
        );

        return $this;
    }
}
