<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @version 1.0.0
 * @copyright Copyright (c) 2018 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

class Mulberry_Warranty_Helper_Item_Updater extends Mage_Core_Helper_Abstract
{
    /**
     * List of attributes to be displayed as "Additional Options" within warranty item
     *
     * @var array $warrantyAdditionalOptions
     */
    protected $warrantyAdditionalOptions = array(
        'service_type',
        'duration_months',
    );

    /**
     * Set custom quote item name for warranty product
     *
     * @param Mage_Sales_Model_Quote_Item $quoteItem
     *
     * @return Mage_Sales_Model_Quote_Item
     */
    public function updateWarrantyProductName(Mage_Sales_Model_Quote_Item $quoteItem)
    {
        /**
         * @var $itemOptionHelper Mulberry_Warranty_Helper_Item_Option_Helper
         */
        $itemOptionHelper = Mage::helper('mulberry_warranty/item_option_helper');

        if ($warrantyOptions = $itemOptionHelper->getWarrantyOption($quoteItem)) {
            $optionsInformation = $warrantyOptions->getData();

            if (isset($optionsInformation['warranty_product']['name'])) {
                $quoteItem->setName($optionsInformation['warranty_product']['name']);
            }
        }

        return $quoteItem;
    }

    /**
     * Assign custom price to warranty product
     *
     * @param Mage_Sales_Model_Quote_Item $warrantyQuoteItem
     * @param array $options
     *
     * @return Mage_Sales_Model_Quote_Item
     */
    public function setCustomWarrantyItemPrice(Mage_Sales_Model_Quote_Item $warrantyQuoteItem, array $options = array())
    {
        $warrantyQuoteItem->setCustomPrice($options['warranty_product']['warranty_price']);
        $warrantyQuoteItem->setOriginalCustomPrice($options['warranty_product']['warranty_price']);
        $warrantyQuoteItem->getProduct()->setIsSuperMode(true);

        return $warrantyQuoteItem;
    }

    /**
     * Add warranty specific option information to Magento product,
     * should be executed, before calling addProduct method while adding warranty product to cart
     *
     * @param Mage_Catalog_Model_Product $warrantyProduct
     * @param array $options
     *
     * @return Mage_Catalog_Model_Product
     */
    public function addWarrantyItemOption(Mage_Catalog_Model_Product $warrantyProduct, array $options = array())
    {
        $warrantyProduct->addCustomOption(
            Mulberry_Warranty_Helper_Item_Option_Helper::WARRANTY_INFORMATION_OPTION_CODE,
            serialize($options)
        );

        return $warrantyProduct;
    }

    /**
     * Add some of the attributes as additional options in order to show them to the customer on the FE,
     * for example, warranty duration & service type
     *
     * @param Mage_Catalog_Model_Product $warrantyProduct
     * @param array $options
     *
     * @return Mage_Catalog_Model_Product
     */
    public function addAdditionalOptions(Mage_Catalog_Model_Product $warrantyProduct, array $options = array())
    {
        $additionalOptions = array();

        foreach ($options as $key => $value) {
            if (in_array($key, $this->warrantyAdditionalOptions)) {
                $label = ucwords(str_replace('_', ' ', $key));

                $additionalOptions[] = array(
                    'label' => $this->__($label),
                    'value' => $value,
                );
            }
        }

        $warrantyProduct->addCustomOption(
            'additional_options',
            serialize($additionalOptions)
        );

        return $warrantyProduct;
    }
}
