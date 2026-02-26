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

namespace Orangecat\PricesList\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Actions column for the companies listing in the Price List edit form.
 * Provides an "Edit Company" link for each row.
 */
class PriceListCompanyActions extends Column
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
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['link_id'])) {
                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href'  => $this->urlBuilder->getUrl(
                                'mycompany/company/edit',
                                ['entity_id' => $item['company_id']]
                            ),
                            'label' => __('Edit Company'),
                        ],
                    ];
                }
            }
        }

        return $dataSource;
    }
}
