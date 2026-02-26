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

namespace Orangecat\PricesList\Model\PriceList\Company;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider as UiDataProvider;

/**
 * DataProvider for the companies listing inside the Price List edit form.
 * Filters by the price_list_id (entity_id) present in the edit page URL.
 */
class DataProvider extends UiDataProvider
{
    /**
     * @inheritdoc
     */
    public function getData(): array
    {
        $this->addPriceListFilter();
        return parent::getData();
    }

    /**
     * Filter the collection by the current price list ID.
     */
    private function addPriceListFilter(): void
    {
        $priceListId = (int)$this->request->getParam('price_list_id')
            ?: (int)$this->request->getParam('entity_id');

        $this->addFilter(
            $this->filterBuilder
                ->setField('price_list_id')
                ->setValue($priceListId > 0 ? $priceListId : -1)
                ->setConditionType('eq')
                ->create()
        );
    }
}
