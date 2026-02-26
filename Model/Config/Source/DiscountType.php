<?php

/**
 * This file is part of the Orangecat PricesList package.
 *
 * (c) Oliverio Gombert <olivertar@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Orangecat\PricesList\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class DiscountType implements OptionSourceInterface
{
    public const FIXED_AMOUNT = 'fixed_amount';
    public const PERCENTAGE = 'percentage';
    public const FIXED_PRICE = 'fixed_price';

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::FIXED_AMOUNT, 'label' => __('Fixed Amount Discount')],
            ['value' => self::PERCENTAGE, 'label' => __('Percentage Discount')],
            ['value' => self::FIXED_PRICE, 'label' => __('Fixed Price Override')]
        ];
    }
}
