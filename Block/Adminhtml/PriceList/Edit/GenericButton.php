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

namespace Orangecat\PricesList\Block\Adminhtml\PriceList\Edit;

use Magento\Backend\Block\Widget\Context;
use Orangecat\PricesList\Api\PriceListRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class GenericButton
{
    /**
     * @param Context $context
     * @param PriceListRepositoryInterface $priceListRepository
     */
    public function __construct(
        protected readonly Context $context,
        protected readonly PriceListRepositoryInterface $priceListRepository
    ) {
    }

    /**
     * Return Price List ID
     *
     * @return int|null
     */
    public function getPriceListId()
    {
        try {
            return $this->priceListRepository->getById(
                $this->context->getRequest()->getParam('entity_id')
            )->getId();
        } catch (NoSuchEntityException $e) {
            unset($e); // Price list not found, ignore
        }
        return null;
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
