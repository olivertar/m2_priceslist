<?php

/**
 * This file is part of the Orangecat PricesList package.
 *
 * (c) Oliverio Gombert <olivertar@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Orangecat\PricesList\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class CompanyPriceListActions extends Column
{
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        protected readonly UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['link_id'])) {
                    $item[$this->getData('name')] = [
                        'delete' => [
                            'href' => $this->urlBuilder->getUrl(
                                'orangecat_priceslist/company_pricelist/delete',
                                ['link_id' => $item['link_id']]
                            ),
                            'label' => __('Remove'),
                            'confirm' => [
                                'title' => __('Remove Price List'),
                                'message' => __('Are you sure you want to remove this price list from the company?')
                            ],
                            'post' => true
                        ]
                    ];
                }
            }
        }
        return $dataSource;
    }
}
