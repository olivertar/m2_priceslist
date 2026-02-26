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

interface PriceListInterface
{
    public const ENTITY_ID = 'entity_id';
    public const NAME = 'name';
    public const CODE = 'code';
    public const IS_ACTIVE = 'is_active';
    public const DESCRIPTION = 'description';
    public const START_DATE = 'start_date';
    public const END_DATE = 'end_date';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    /**
     * Get Entity ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set Entity ID
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get Entity ID
     *
     * @return int|null
     */
    public function getEntityId();

    /**
     * Set Entity ID
     *
     * @param int $entityId
     * @return $this
     */
    public function setEntityId($entityId);

    /**
     * Get Name
     *
     * @return string|null
     */
    public function getName();

    /**
     * Set Name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Get Code
     *
     * @return string|null
     */
    public function getCode();

    /**
     * Set Code
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code);

    /**
     * Get Is Active
     *
     * @return bool|null
     */
    public function getIsActive();

    /**
     * Set Is Active
     *
     * @param bool|int $isActive
     * @return $this
     */
    public function setIsActive($isActive);

    /**
     * Get Description
     *
     * @return string|null
     */
    public function getDescription();

    /**
     * Set Description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * Get Start Date
     *
     * @return string|null
     */
    public function getStartDate();

    /**
     * Set Start Date
     *
     * @param string $startDate
     * @return $this
     */
    public function setStartDate($startDate);

    /**
     * Get End Date
     *
     * @return string|null
     */
    public function getEndDate();

    /**
     * Set End Date
     *
     * @param string $endDate
     * @return $this
     */
    public function setEndDate($endDate);

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
