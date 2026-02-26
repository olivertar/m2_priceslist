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

namespace Orangecat\PricesList\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Mode implements OptionSourceInterface
{
    public const MODE_CASCADE = 'cascade';
    public const MODE_WEIGHT  = 'weight';

    /**
     * Get options
     *
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => self::MODE_CASCADE, 'label' => __('Cascade')],
            ['value' => self::MODE_WEIGHT, 'label' => __('Weight')],
        ];
    }
}
