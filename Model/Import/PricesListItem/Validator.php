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

namespace Orangecat\PricesList\Model\Import\PricesListItem;

use Magento\Framework\Validator\AbstractValidator;

class Validator extends AbstractValidator
{
    /**
     * Validator for imported item.
     *
     * @param array $value
     * @return bool
     */
    public function isValid($value)
    {
        $this->_clearMessages();

        $valid = true;

        if (!isset($value['price_list_code']) || empty(trim($value['price_list_code']))) {
            $this->_addMessages(['Price List Code is required']);
            $valid = false;
        }

        if (!isset($value['sku']) || empty(trim($value['sku']))) {
            $this->_addMessages(['SKU is required']);
            $valid = false;
        }

        if (!isset($value['qty']) || !is_numeric($value['qty']) || (float)$value['qty'] < 0) {
            $this->_addMessages(['Qty must be a valid positive number or zero']);
            $valid = false;
        }

        if (!isset($value['amount']) || !is_numeric($value['amount']) || (float)$value['amount'] < 0) {
            $this->_addMessages(['Amount must be a valid positive number or zero']);
            $valid = false;
        }

        $discountType = isset($value['discount_type']) && !empty($value['discount_type'])
            ? $value['discount_type']
            : 'fixed_price';

        $validTypes = ['fixed_price', 'fixed_amount', 'percentage'];
        if (!in_array($discountType, $validTypes)) {
            $this->_addMessages(['Invalid discount type. Allowed: fixed_price, fixed_amount, percentage']);
            $valid = false;
        }

        return $valid;
    }
}
