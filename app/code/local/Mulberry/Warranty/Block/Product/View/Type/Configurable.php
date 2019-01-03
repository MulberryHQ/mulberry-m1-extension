<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2018 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

class Mulberry_Warranty_Block_Product_View_Type_Configurable extends Mage_Catalog_Block_Product_View_Type_Configurable
{
    /**
     * Extended configurable product information with appropriate simple SKUs
     *
     * @return array
     */
    protected function _getAdditionalConfig()
    {
        $configuration = array();

        foreach ($this->getAllowProducts() as $simpleProduct) {
            $configuration['simple_skus'][$simpleProduct->getId()] = $simpleProduct->getSku();
        }

        return $configuration;
    }
}
