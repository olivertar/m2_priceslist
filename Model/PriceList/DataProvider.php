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

namespace Orangecat\PricesList\Model\PriceList;

use Orangecat\PricesList\Model\ResourceModel\PriceList\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param RequestInterface $request
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        private readonly DataPersistorInterface $dataPersistor,
        private readonly RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $model) {
            $this->loadedData[$model->getId()] = $model->getData();
        }
        $data = $this->dataPersistor->get('orangecat_priceslist_pricelist');
        if (!empty($data)) {
            $model = $this->collection->getNewEmptyItem();
            $model->setData($data);
            $this->loadedData[$model->getId()] = $model->getData();
            $this->dataPersistor->clear('orangecat_priceslist_pricelist');
        }
        return $this->loadedData;
    }

    /**
     * Hide the Items fieldset when creating a new price list (no entity_id yet).
     *
     * Products can only be associated after the list has been saved.
     *
     * @return array
     */
    public function getMeta(): array
    {
        $meta = parent::getMeta();

        // Hide the Items and Companies fieldsets when creating a new price list.
        // entity_id is present in the URL only for existing records.
        $isNewRecord = !(int)$this->request->getParam('entity_id');
        if ($isNewRecord) {
            $meta['items']['arguments']['data']['config']['visible'] = false;
            $meta['items']['arguments']['data']['config']['componentType'] = 'fieldset';
            $meta['companies']['arguments']['data']['config']['visible'] = false;
            $meta['companies']['arguments']['data']['config']['componentType'] = 'fieldset';
        }

        return $meta;
    }
}
