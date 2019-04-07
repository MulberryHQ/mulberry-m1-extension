<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2018 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */
/* @var $this Mage_Eav_Model_Entity_Setup */

class Warranty_Product_Setup
{
    const WARRANTY_PRODUCT_SKU = 'mulberry-warranty-product';

    /**
     * @param Mage_Eav_Model_Entity_Setup $installer
     */
    public function run(Mage_Eav_Model_Entity_Setup $installer)
    {
        $this->updateAttributes($installer);
        $this->createWarrantyProduct();
    }

    /**
     * Create warranty product placeholder
     */
    private function createWarrantyProduct()
    {
        if (!$product = Mage::getModel('catalog/product')->loadByAttribute('sku', self::WARRANTY_PRODUCT_SKU)) {
            /**
             * @var $product Mage_Catalog_Model_Product
             */
            $product = Mage::getModel('catalog/product');

            $product->setTypeId(Mulberry_Warranty_Model_Product_Type_Warranty::TYPE_ID)
                ->setSku(self::WARRANTY_PRODUCT_SKU)
                ->setName('Mulberry Warranty Product')
                ->setDescription('Mulberry Warranty Product Placeholder')
                ->setShortDescription('Mulberry Warranty Product Placeholder')
                ->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG)
                ->setAttributeSetId(4)
                ->setTaxClassId(0)
                ->setPrice(10.00)
                ->setStockData(array(
                    'use_config_manage_stock' => 0,
                    'manage_stock' => 0,
                    'is_in_stock' => 1,
                    'qty' => 10,
                ));

            $product->save();
        }
    }

    /**
     * @param Mage_Eav_Model_Entity_Setup $installer
     */
    private function updateAttributes(Mage_Eav_Model_Entity_Setup $installer)
    {
        $fieldList = array(
            'price',
        );

        /**
         * Make these attributes applicable to warranty products
         */
        foreach ($fieldList as $field) {
            if ($attributeApplyTo = $installer->getAttribute(Mage_Catalog_Model_Product::ENTITY, $field, 'apply_to')) {
                $applyTo = explode(
                    ',',
                    $attributeApplyTo
                );

                if (!in_array(Mulberry_Warranty_Model_Product_Type_Warranty::TYPE_ID, $applyTo)) {
                    $applyTo[] = Mulberry_Warranty_Model_Product_Type_Warranty::TYPE_ID;

                    $installer->updateAttribute(
                        Mage_Catalog_Model_Product::ENTITY,
                        $field,
                        'apply_to',
                        implode(',', $applyTo)
                    );
                }
            }
        }
    }
}

$setup = new Warranty_Product_Setup();
$setup->run($this);
