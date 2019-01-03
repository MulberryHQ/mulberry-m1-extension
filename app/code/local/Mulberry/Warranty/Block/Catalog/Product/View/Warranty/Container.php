<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2018 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

class Mulberry_Warranty_Block_Catalog_Product_View_Warranty_Container extends Mage_Catalog_Block_Product_View
{
    /**
     * Do not output block, if it's not activated in admin
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!Mage::helper('mulberry_warranty')->isActive() || !$this->getPartnerUrl() || !$this->getApiUrl()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * @return null|string
     */
    public function getPartnerUrl()
    {
        return Mage::helper('mulberry_warranty')->getPartnerUrl();
    }

    /**
     * @return null|string
     */
    public function getApiUrl()
    {
        return Mage::helper('mulberry_warranty')->getApiUrl();
    }

    /**
     * @return mixed
     */
    public function getPlatformDomain()
    {
        return Mage::helper('mulberry_warranty')->getPlatformDomain() ?: $_SERVER['SERVER_NAME'];
    }
}
