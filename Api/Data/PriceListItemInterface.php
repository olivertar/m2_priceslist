<?php

/**
 * This file is part of the Orangecat PricesList package.
 *
 * (c) Oliverio Gombert <olivertar@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Orangecat\PricesList\Api\Data;

interface PriceListItemInterface
{
    public const ITEM_ID = 'item_id';
    public const PRICE_LIST_ID = 'price_list_id';
    public const SKU = 'sku';
    public const DISCOUNT_TYPE = 'discount_type';
    public const AMOUNT = 'amount';
    public const QTY = 'qty';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    /**
     * Get Item ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set Item ID
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get Price List ID
     *
     * @return int|null
     */
    public function getPriceListId();

    /**
     * Set Price List ID
     *
     * @param int $priceListId
     * @return $this
     */
    public function setPriceListId($priceListId);

    /**
     * Get SKU
     *
     * @return string|null
     */
    public function getSku();

    /**
     * Set SKU
     *
     * @param string $sku
     * @return $this
     */
    public function setSku($sku);

    /**
     * Get Discount Type
     *
     * @return string|null
     */
    public function getDiscountType();

    /**
     * Set Discount Type
     *
     * @param string $discountType
     * @return $this
     */
    public function setDiscountType($discountType);

    /**
     * Get Amount
     *
     * @return float|null
     */
    public function getAmount();

    /**
     * Set Amount
     *
     * @param float $amount
     * @return $this
     */
    public function setAmount($amount);

    /**
     * Get Qty
     *
     * @return float|null
     */
    public function getQty();

    /**
     * Set Qty
     *
     * @param float $qty
     * @return $this
     */
    public function setQty($qty);

    /**
     * Get Created At
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set Created At
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get Updated At
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set Updated At
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);
}
