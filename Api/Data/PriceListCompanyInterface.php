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

interface PriceListCompanyInterface
{
    public const LINK_ID = 'link_id';
    public const COMPANY_ID = 'company_id';
    public const PRICE_LIST_ID = 'price_list_id';
    public const PRIORITY = 'priority';

    /**
     * Get Link ID
     *
     * @return int|null
     */
    public function getLinkId();

    /**
     * Set Link ID
     *
     * @param int $id
     * @return $this
     */
    public function setLinkId($id);

    /**
     * Get Company ID
     *
     * @return int|null
     */
    public function getCompanyId();

    /**
     * Set Company ID
     *
     * @param int $companyId
     * @return $this
     */
    public function setCompanyId($companyId);

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
     * Get Priority
     *
     * @return int|null
     */
    public function getPriority();

    /**
     * Set Priority
     *
     * @param int $priority
     * @return $this
     */
    public function setPriority($priority);
}
