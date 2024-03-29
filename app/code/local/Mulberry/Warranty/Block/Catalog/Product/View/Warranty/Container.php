<?php
/**
 * @category Mulberry
 * @package Mulberry_Warranty
 * @author Mulberry <support@getmulberry.com>
 * @version 1.0.0
 * @copyright Copyright (c) 2021 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
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

    /**
     * @return mixed
     */
    public function getRetailerId()
    {
        return Mage::helper('mulberry_warranty')->getRetailerId();
    }

    /**
     * @return mixed
     */
    public function getPublicToken()
    {
        return Mage::helper('mulberry_warranty')->getPublicToken();
    }

    /**
     * @param $product
     * @return string
     */
    public function getProductDescription()
    {
        $product = $this->getProduct();
        $description = $this->stripTags($product->getDescription()); // Strip HTML tags
        $description = str_replace(["\r", "\n"], '', $description); // Remove new lines

        return substr($description, 0, 255);
    }

    /**
     * Return product image gallery info
     *
     * @return false|string
     */
    public function getGalleryImagesInfo()
    {
        return json_encode(Mage::helper('mulberry_warranty/product')->getGalleryImagesInfo($this->getProduct()));
    }

    /**
     * Return product breadcrumbs
     *
     * @return false|string
     */
    public function getBreadcrumbsInfo()
    {
        return json_encode(Mage::helper('mulberry_warranty/product')->getProductBreadcrumbs($this->getProduct()));
    }
}
