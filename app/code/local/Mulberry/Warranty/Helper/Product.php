<?php

/**
 * @category Mulberry
 * @package Mulberry_Warranty
 * @author Mulberry <support@getmulberry.com>
 * @version 1.3.0
 * @copyright Copyright (c) 2022 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */
class Mulberry_Warranty_Helper_Product extends Mage_Core_Helper_Abstract
{
    /**
     * Return product category breadcrumbs information
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getProductBreadcrumbs(Mage_Catalog_Model_Product $product)
    {
        $categories = $product->getCategoryCollection()->addAttributeToSelect('name');
        $result = [];

        foreach ($categories as $category) {
            $result[] = [
                'category' => $category->getName(),
                'url' => $category->getUrl(),
            ];
        }

        return $result;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return array|Varien_Data_Collection
     */
    public function getGalleryImagesInfo(Mage_Catalog_Model_Product $product)
    {
        $result = [];
        $images = $product->getMediaGalleryImages();
        if (!$images instanceof Varien_Data_Collection) {
            return $images;
        }

        foreach ($images as $image) {
            $image->setData(
                'url',
                (string) Mage::helper('catalog/image')->init($product, 'image', $image->getFile())->resize(700)
            );
            $result[] = ['src' => $image->getUrl()];
        }

        return $result;
    }
}
