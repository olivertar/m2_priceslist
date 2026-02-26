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
use Orangecat\PricesList\Api\Data\PriceListItemInterface;

class PriceListItem extends AbstractModel implements PriceListItemInterface, IdentityInterface
{
    public const CACHE_TAG = 'orangecat_priceslist_item';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * @var string
     */
    protected $_eventPrefix = 'orangecat_priceslist_item';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Orangecat\PricesList\Model\ResourceModel\PriceListItem::class);
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
    public function getSku()
    {
        return $this->getData(self::SKU);
    }

    /**
     * @inheritDoc
     */
    public function setSku($sku)
    {
        return $this->setData(self::SKU, $sku);
    }

    /**
     * @inheritDoc
     */
    public function getDiscountType()
    {
        return $this->getData(self::DISCOUNT_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setDiscountType($discountType)
    {
        return $this->setData(self::DISCOUNT_TYPE, $discountType);
    }

    /**
     * @inheritDoc
     */
    public function getAmount()
    {
        return $this->getData(self::AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setAmount($amount)
    {
        return $this->setData(self::AMOUNT, $amount);
    }

    /**
     * @inheritDoc
     */
    public function getQty()
    {
        return $this->getData(self::QTY);
    }

    /**
     * @inheritDoc
     */
    public function setQty($qty)
    {
        return $this->setData(self::QTY, $qty);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * Set timestamps before save
     *
     * @return $this
     */
    protected function _beforeSave()
    {
        $now = (new \DateTime())->format('Y-m-d H:i:s');
        if (!$this->getId()) {
            $this->setCreatedAt($now);
        }
        $this->setUpdatedAt($now);
        return parent::_beforeSave();
    }
}
