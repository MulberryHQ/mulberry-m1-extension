<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Dmitrijs Sitovs <info@scandiweb.com / dmitrijssh@scandiweb.com / dsitovs@gmail.com>
 * @copyright Copyright (c) 2018 Scandiweb, Ltd (http://scandiweb.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

class Mulberry_Warranty_Block_Product_View_Options extends Mage_Catalog_Block_Product_View_Options
{
    /**
     * Extend custom options with option SKU information
     *
     * @param Mage_Catalog_Model_Product_Option_Value|Mage_Catalog_Model_Product_Option $option
     *
     * @return array
     */
    protected function _getPriceConfiguration($option)
    {
        $data = parent::_getPriceConfiguration($option);

        if ($valueSku = $option->getSku()) {
            $data['value_sku'] = $valueSku;
        }

        return $data;
    }
}
