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

namespace Orangecat\PricesList\Plugin\Company\Controller\Adminhtml\Company;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Request\Http as HttpRequest;
use Psr\Log\LoggerInterface;

/**
 * Plugin on Company Save controller.
 *
 * Reads two POST arrays produced by the PriceListGrid block:
 *  - price_list_ids[{id}]      : value 1 if checked, not sent if unchecked
 *  - price_list_priority[{id}] : numeric priority for each list
 *
 * Then atomically replaces the priceslist_company rows.
 */
class SavePriceLists
{
    /**
     * @param ResourceConnection $resourceConnection
     * @param HttpRequest $request
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly ResourceConnection $resourceConnection,
        private readonly HttpRequest $request,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Save price lists after company save
     *
     * @param \Orangecat\Company\Controller\Adminhtml\Company\Save $subject
     * @param \Magento\Framework\Controller\ResultInterface $result
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function afterExecute(
        \Orangecat\Company\Controller\Adminhtml\Company\Save $subject,
        $result
    ) {
        $postData  = $this->request->getPostValue();

        // company_id: may come from general fieldset or root
        $companyId = (int)(
            $postData['general']['entity_id']
            ?? $postData['entity_id']
            ?? 0
        );

        if (!$companyId) {
            return $result;
        }

        // The UI Component now submits these directly to the root of the POST payload
        // because of `data-form-part` in the widget grid column renderers.
        $selectedIdsArray = $postData['price_list_ids'] ?? [];
        $selectedIds = [];
        if (is_array($selectedIdsArray)) {
            foreach ($selectedIdsArray as $id => $isChecked) {
                if ((int)$isChecked === 1) {
                    $selectedIds[] = (int)$id;
                }
            }
        }

        $priorityMap = is_array($postData['price_list_priority'] ?? null)
            ? $postData['price_list_priority']
            : [];

        $connection    = $this->resourceConnection->getConnection();
        $table         = $this->resourceConnection->getTableName('priceslist_company');
        $companyTable  = $this->resourceConnection->getTableName('mycompany');

        $companyExists = (int)$connection->fetchOne(
            $connection->select()->from($companyTable, ['entity_id'])->where('entity_id = ?', $companyId)
        );

        if (!$companyExists) {
            $this->logger->warning(
                'PricesList: company_id not found, skipping price list assignment.',
                ['company_id' => $companyId]
            );
            return $result;
        }

        $connection->beginTransaction();
        try {
            $connection->delete($table, ['company_id = ?' => $companyId]);

            if (!empty($selectedIds)) {
                $rows = [];
                foreach ($selectedIds as $plId) {
                    $rows[] = [
                        'company_id'    => $companyId,
                        'price_list_id' => $plId,
                        'priority'      => (int)($priorityMap[$plId] ?? 0),
                    ];
                }
                $connection->insertMultiple($table, $rows);
            }

            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            $this->logger->error(
                'PricesList: failed to save company price list assignments.',
                ['exception' => $e->getMessage(), 'company_id' => $companyId]
            );
        }

        return $result;
    }
}
