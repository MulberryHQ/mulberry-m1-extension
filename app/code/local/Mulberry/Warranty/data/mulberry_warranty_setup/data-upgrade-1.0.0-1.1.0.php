<?php
/**
 * @category Mulberry
 * @package Mulberry_Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2021 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */
/* @var $this Mage_Eav_Model_Entity_Setup */

class Mulberry_Warranty_Product_Placeholder_Setup
{
    private $warrantyProductSkus = array(
        'mulberry-warranty-12-months' => 'Mulberry Warranty Product - 12 Months',
        'mulberry-warranty-24-months' => 'Mulberry Warranty Product - 24 Months',
        'mulberry-warranty-36-months' => 'Mulberry Warranty Product - 36 Months',
        'mulberry-warranty-48-months' => 'Mulberry Warranty Product - 48 Months',
        'mulberry-warranty-60-months' => 'Mulberry Warranty Product - 60 Months',
    );

    /**
     * @param Mage_Eav_Model_Entity_Setup $installer
     */
    public function run(Mage_Eav_Model_Entity_Setup $installer)
    {
        $this->updateAttributes($installer);
        $this->createWarrantyProduct();
    }

    /**
     * Create appropriate warranty product placeholders
     */
    private function createWarrantyProduct()
    {
        /**
         * @var $product Mage_Catalog_Model_Product
         */
        foreach ($this->warrantyProductSkus as $warrantyProductSku => $productName) {
            if (!$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $warrantyProductSku)) {
                $product = Mage::getModel('catalog/product');

                $product->setTypeId(Mulberry_Warranty_Model_Product_Type_Warranty::TYPE_ID)
                    ->setSku($warrantyProductSku)
                    ->setName($productName)
                    ->setDescription(sprintf('%s, Product Placeholder', $productName))
                    ->setShortDescription(sprintf('%s, Product Placeholder', $productName))
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

$setup = new Mulberry_Warranty_Product_Placeholder_Setup();
$setup->run($this);
