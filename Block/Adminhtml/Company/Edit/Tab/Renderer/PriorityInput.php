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

namespace Orangecat\PricesList\Block\Adminhtml\Company\Edit\Tab\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

/**
 * Renders a text input for the priority value in the Price List assignment grid.
 * Pre-populates with the existing priority for already-assigned lists.
 */
class PriorityInput extends AbstractRenderer
{
    /**
     * @inheritdoc
     */
    public function render(DataObject $row): string
    {
        $priceListId    = (int)$row->getId();
        $assignedValues = $this->getColumn()->getData('assigned_values') ?? [];

        // Find current priority for this entity
        $priority = 0;
        foreach ($assignedValues as $item) {
            if ((int)($item['price_list_id'] ?? 0) === $priceListId) {
                $priority = (int)$item['priority'];
                break;
            }
        }

        return sprintf(
            '<input type="number" name="price_list_priority[%d]" value="%d" min="0" step="1" '
                . 'data-form-part="mycompany_company_form" style="width:60px;text-align:center;" '
                . 'class="admin__control-text" />',
            $priceListId,
            $priority
        );
    }
}
