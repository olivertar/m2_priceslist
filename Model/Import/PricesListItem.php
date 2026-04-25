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

namespace Orangecat\PricesList\Model\Import;

use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\Import;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class PricesListItem extends AbstractEntity
{
    public const PRICE_LIST_CODE = 'price_list_code';
    public const SKU = 'sku';
    public const QTY = 'qty';
    public const DISCOUNT_TYPE = 'discount_type';
    public const AMOUNT = 'amount';

    public const TABLE_PRICELIST = 'priceslist';
    public const TABLE_ENTITY = 'priceslist_item';

    /**
     * @var bool
     */
    protected $needColumnCheck = true;

    /**
     * @var array
     */
    protected $validColumnNames = [
        self::PRICE_LIST_CODE,
        self::SKU,
        self::QTY,
        self::DISCOUNT_TYPE,
        self::AMOUNT
    ];

    /**
     * @var string
     */
    protected $masterAttributeCode = self::PRICE_LIST_CODE;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * Cache for mapping price_list_code to entity_id to minimize DB queries
     * @var array
     */
    private $priceListCache = [];

    /**
     * @var array
     */
    private $skuCache = [];

    /**
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\ImportExport\Helper\Data $importExportData
     * @param \Magento\ImportExport\Model\ResourceModel\Import\Data $importData
     * @param ResourceConnection $resource
     * @param \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\ImportExport\Helper\Data $importExportData,
        \Magento\ImportExport\Model\ResourceModel\Import\Data $importData,
        ResourceConnection $resource,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Stdlib\StringUtils $string,
        ProcessingErrorAggregatorInterface $errorAggregator,
        private ProductRepositoryInterface $productRepository
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->_importExportData = $importExportData;
        $this->_resourceHelper = $resourceHelper;
        $this->_dataSourceModel = $importData;
        $this->errorAggregator = $errorAggregator;
        $this->connection = $resource->getConnection();
    }

    /**
     * Get valid column names
     *
     * @return array
     */
    public function getValidColumnNames()
    {
        return $this->validColumnNames;
    }

    /**
     * Get entity type code
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'priceslist_item';
    }

    /**
     * Import data
     *
     * @return bool
     */
    protected function _importData()
    {
        if (Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            $this->deleteEntity();
        } elseif (Import::BEHAVIOR_REPLACE == $this->getBehavior()) {
            $this->replaceEntity();
        } elseif (Import::BEHAVIOR_APPEND == $this->getBehavior()) {
            $this->saveEntity();
        }

        return true;
    }

    /**
     * Retrieves the Price List ID from its Code.
     *
     * Caches the result to avoid multiple queries for the same code.
     *
     * @param string $code
     * @return int|bool Returns the ID if found, false otherwise.
     */
    private function getPriceListIdByCode(string $code)
    {
        if (!isset($this->priceListCache[$code])) {
            $select = $this->connection->select()
                ->from($this->connection->getTableName(self::TABLE_PRICELIST), ['entity_id'])
                ->where('code = ?', $code);

            $id = $this->connection->fetchOne($select);

            if ($id) {
                $this->priceListCache[$code] = (int)$id;
            } else {
                $this->priceListCache[$code] = false;
            }
        }

        return $this->priceListCache[$code];
    }

    /**
     * Validate row
     *
     * @param array $rowData
     * @param int $rowNum
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum)
    {
        if (isset($this->_validatedRows[$rowNum])) {
            return !$this->getErrorAggregator()->isRowInvalid($rowNum);
        }

        $this->_validatedRows[$rowNum] = true;

        if (!isset($rowData[self::PRICE_LIST_CODE]) || empty(trim($rowData[self::PRICE_LIST_CODE]))) {
            $this->addRowError('Price List Code is missing', $rowNum);
            return false;
        }

        if (!isset($rowData[self::SKU]) || empty(trim($rowData[self::SKU]))) {
            $this->addRowError('SKU is missing', $rowNum);
            return false;
        }

        if (!isset($rowData[self::QTY]) || $rowData[self::QTY] === '') {
            $this->addRowError('Qty is missing', $rowNum);
            return false;
        }

        if (!isset($rowData[self::AMOUNT]) || $rowData[self::AMOUNT] === '') {
            $this->addRowError('Amount is missing', $rowNum);
            return false;
        }

        $discountType = isset($rowData[self::DISCOUNT_TYPE]) && !empty(trim($rowData[self::DISCOUNT_TYPE]))
            ? trim($rowData[self::DISCOUNT_TYPE])
            : 'fixed_price';

        $validTypes = ['fixed_price', 'fixed_amount', 'percentage'];
        if (!in_array($discountType, $validTypes)) {
            $this->addRowError('Invalid discount type. Allowed: fixed_price, fixed_amount, percentage', $rowNum);
            return false;
        }

        $priceListCode = trim($rowData[self::PRICE_LIST_CODE]);
        $priceListId = $this->getPriceListIdByCode($priceListCode);

        if (!$priceListId) {
            $this->addRowError('Price List Code does not exist', $rowNum);
            return false;
        }

        $sku = trim($rowData[self::SKU]);
        if (!isset($this->skuCache[$sku])) {
            try {
                $this->productRepository->get($sku);
                $this->skuCache[$sku] = true;
            } catch (NoSuchEntityException $e) {
                $this->addRowError('Product SKU does not exist', $rowNum);
                $this->skuCache[$sku] = false;
                return false;
            }
        } elseif (!$this->skuCache[$sku]) {
            $this->addRowError('Product SKU does not exist', $rowNum);
            return false;
        }

        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }

    /**
     * Save entity
     *
     * @return $this
     */
    protected function saveEntity()
    {
        $this->saveAndReplaceEntity();
        return $this;
    }

    /**
     * Replace entity
     *
     * @return $this
     */
    protected function replaceEntity()
    {
        $this->saveAndReplaceEntity();
        return $this;
    }

    /**
     * Save and replace entity
     *
     * @return void
     */
    private function saveAndReplaceEntity()
    {
        $behavior = $this->getBehavior();
        $listData = [];

        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->validateRow($rowData, $rowNum)) {
                    continue;
                }

                $priceListCode = trim($rowData[self::PRICE_LIST_CODE]);
                $priceListId = $this->getPriceListIdByCode($priceListCode);

                if (!$priceListId) {
                    continue; // Double check, though validateRow should have caught it
                }

                $sku = trim($rowData[self::SKU]);
                $qty = (float) $rowData[self::QTY];
                $amount = (float) $rowData[self::AMOUNT];

                $discountType = isset($rowData[self::DISCOUNT_TYPE]) && !empty(trim($rowData[self::DISCOUNT_TYPE]))
                    ? trim($rowData[self::DISCOUNT_TYPE])
                    : 'fixed_price';

                $listData[] = [
                    'price_list_id' => $priceListId,
                    self::SKU => $sku,
                    self::QTY => $qty,
                    self::DISCOUNT_TYPE => $discountType,
                    self::AMOUNT => $amount,
                    'updated_at' => (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
                ];
            }

            if (!empty($listData)) {
                $tableName = $this->connection->getTableName(self::TABLE_ENTITY);
                // In Magento, insertOnDuplicate takes the unique columns.
                // Our unique index/constraint is usually handled by the ORM, but natively this module DB schema
                // might not have a composite unique key for (price_list_id, sku, qty).
                // Let's assume the combination of price_list_id, sku, qty should be unique per tier logic.
                $this->connection->insertOnDuplicate($tableName, $listData, [
                    self::DISCOUNT_TYPE,
                    self::AMOUNT,
                    'updated_at'
                ]);

                if (Import::BEHAVIOR_APPEND == $behavior) {
                    $this->countItemsCreated += count($listData);
                } elseif (Import::BEHAVIOR_REPLACE == $behavior) {
                    $this->countItemsUpdated += count($listData);
                }

                $listData = [];
            }
        }
    }

    /**
     * Delete entity
     *
     * @return $this
     */
    protected function deleteEntity()
    {
        $listData = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->validateRow($rowData, $rowNum)) {
                    continue;
                }

                $priceListCode = trim($rowData[self::PRICE_LIST_CODE]);
                $priceListId = $this->getPriceListIdByCode($priceListCode);

                $listData[] = [
                    'price_list_id' => $priceListId,
                    self::SKU => trim($rowData[self::SKU]),
                    self::QTY => (float) $rowData[self::QTY]
                ];
            }

            if (!empty($listData)) {
                $tableName = $this->connection->getTableName(self::TABLE_ENTITY);
                foreach ($listData as $record) {
                    $this->connection->delete(
                        $tableName,
                        [
                            $this->connection->quoteInto('price_list_id = ?', $record['price_list_id']),
                            $this->connection->quoteInto('sku = ?', $record[self::SKU]),
                            $this->connection->quoteInto('qty = ?', $record[self::QTY])
                        ]
                    );
                    $this->countItemsDeleted++;
                }
                $listData = [];
            }
        }
        return $this;
    }
}
