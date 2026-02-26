<?php

/**
 * This file is part of the Orangecat PricesList package.
 *
 * (c) Oliverio Gombert <olivertar@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Orangecat\PricesList\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;
use Orangecat\PricesList\Api\Data\PriceListCompanyInterface;

class PriceListCompany extends AbstractModel implements PriceListCompanyInterface, IdentityInterface
{
    public const CACHE_TAG = 'orangecat_priceslist_company';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * @var string
     */
    protected $_eventPrefix = 'orangecat_priceslist_company';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Orangecat\PricesList\Model\ResourceModel\PriceListCompany::class);
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @inheritDoc
     */
    public function getLinkId()
    {
        return $this->getData(self::LINK_ID);
    }

    /**
     * @inheritDoc
     */
    public function setLinkId($id)
    {
        return $this->setData(self::LINK_ID, $id);
    }

    /**
     * @inheritDoc
     */
    public function getCompanyId()
    {
        return $this->getData(self::COMPANY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCompanyId($companyId)
    {
        return $this->setData(self::COMPANY_ID, $companyId);
    }

    /**
     * @inheritDoc
     */
    public function getPriceListId()
    {
        return $this->getData(self::PRICE_LIST_ID);
    }

    /**
     * @inheritDoc
     */
    public function setPriceListId($priceListId)
    {
        return $this->setData(self::PRICE_LIST_ID, $priceListId);
    }

    /**
     * @inheritDoc
     */
    public function getPriority()
    {
        return $this->getData(self::PRIORITY);
    }

    /**
     * @inheritDoc
     */
    public function setPriority($priority)
    {
        return $this->setData(self::PRIORITY, $priority);
    }
}
