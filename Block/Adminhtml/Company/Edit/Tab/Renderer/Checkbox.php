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

use Magento\Backend\Block\Widget\Grid\Column\Renderer\Checkbox as BaseCheckbox;
use Magento\Framework\DataObject;

/**
 * Custom Checkbox renderer for PriceListGrid.
 * Injects `data-form-part="mycompany_company_form"` into the HTML input tags
 * so the UI Component form will correctly serialize these fields.
 */
class Checkbox extends BaseCheckbox
{
    /**
     * @inheritdoc
     */
    public function render(DataObject $row): string
    {
        $id = (int)$row->getId();

        // Determine if this row should be pre-checked
        $values = (array)$this->getColumn()->getData('values');
        $isChecked = in_array($id, $values, false);
        $checked   = $isChecked ? ' checked="checked"' : '';

        $html  = '<label class="data-grid-checkbox-cell-inner" for="id_' . $id . '">';
        $html .= '<input type="checkbox"';
        $html .= ' class="company-pricelist-checkbox admin__control-checkbox"';
        $html .= ' data-form-part="mycompany_company_form"';
        $html .= ' name="price_list_ids[' . $id . ']"';
        $html .= ' value="1"';
        $html .= ' id="id_' . $id . '"';
        $html .= $checked . '/>';
        $html .= '<label for="id_' . $id . '"></label>';
        $html .= '</label>';

        return $html;
    }
}
