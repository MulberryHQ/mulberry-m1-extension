<?php

/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @version 1.0.0
 * @copyright Copyright (c) 2019 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */
class Mulberry_Warranty_Block_Adminhtml_Sales_Items_Column_Warranty_Name extends Mage_Adminhtml_Block_Sales_Items_Column_Name
{
    /**
     * Column label mapping for warranty options
     *
     * @var array
     */
    private $warrantyOptionColumns = array(
        'service_type' => 'Service Type',
        'warranty_hash' => 'Warranty Hash',
        'duration_months' => 'Duration (Months)',
    );

    /**
     * Extend options output with appropriate warranty information
     *
     * @return array
     */
    public function getOrderOptions()
    {
        $result = parent::getOrderOptions();

        if ($options = $this->getItem()->getProductOptions()) {
            if (isset($options['info_buyRequest']['warranty_product'])) {
                $warrantyOptions = $options['info_buyRequest']['warranty_product'];
                $formattedWarrantyOptions = array();

                foreach ($this->warrantyOptionColumns as $optionCode => $optionLabel) {
                    $formattedWarrantyOptions[] = array(
                        'label' => $this->__($optionLabel),
                        'value' => $warrantyOptions[$optionCode],
                    );
                }

                $result = array_merge($result, $formattedWarrantyOptions);
            }
        }

        return $result;
    }
}
