<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Dmitrijs Sitovs <info@scandiweb.com / dmitrijssh@scandiweb.com / dsitovs@gmail.com>
 * @copyright Copyright (c) 2018 Scandiweb, Ltd (http://scandiweb.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

class Mulberry_Warranty_Helper_Item_Option_Helper extends Mage_Core_Helper_Abstract
{
    /**
     * Warranty specific item information is stored under this code within quote_item_option table
     */
    const WARRANTY_INFORMATION_OPTION_CODE = 'warranty_information';

    /**
     * @var array $warrantyInformationCache
     */
    private $warrantyInformationCache = array();

    /**
     * Retrieve warranty product option information for the existing quote item
     *
     * @param Mage_Sales_Model_Quote_Item $quoteItem
     *
     * @return Varien_Object
     */
    public function getWarrantyOption(Mage_Sales_Model_Quote_Item $quoteItem)
    {
        $option = $quoteItem->getOptionByCode(self::WARRANTY_INFORMATION_OPTION_CODE);
        $data = $option ? unserialize($option->getValue()) : array();

        return new Varien_Object($data);
    }

    /**
     * Prepare warranty specific option information
     *
     * @param Mage_Sales_Model_Quote_Item $originalQuoteItem
     * @param string $warrantyHash
     *
     * @return array
     */
    public function prepareWarrantyOption(Mage_Sales_Model_Quote_Item $originalQuoteItem, $warrantyHash)
    {
        return [
            'warranty_product' => $this->prepareWarrantyInformation($warrantyHash),
            'original_product' => $this->prepareProductInformation($originalQuoteItem),
        ];
    }

    /**
     * Prepare warranty product information
     *
     * @param $warrantyHash
     *
     * @return array
     */
    public function prepareWarrantyInformation($warrantyHash)
    {
        if (!array_key_exists($warrantyHash, $this->warrantyInformationCache)) {
            if ($warrantyInfo = Mage::getModel('mulberry_warranty/api_rest_warranty')->getWarrantyByHash($warrantyHash)) {
                $this->warrantyInformationCache[$warrantyHash] = $warrantyInfo;
            }
        }

        return $this->warrantyInformationCache[$warrantyHash];
    }

    /**
     * Fetch original product information and save it within warranty product for further processing
     *
     * @param Mage_Sales_Model_Quote_Item $originalQuoteItem
     *
     * @return array
     */
    private function prepareProductInformation(Mage_Sales_Model_Quote_Item $originalQuoteItem)
    {
        $originalProductBuyRequest = $originalQuoteItem->getBuyRequest();

        return [
            'product' => $originalProductBuyRequest->getProduct(),
            'selected_configurable_option' => $originalProductBuyRequest->getSelectedConfigurableOption(),
        ];
    }
}
