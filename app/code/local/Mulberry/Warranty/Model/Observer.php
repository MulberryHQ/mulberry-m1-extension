<?php
/**
 * @category Mulberry
 * @package Mulberry_Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2021 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

class Mulberry_Warranty_Model_Observer
{
    /**
     * Add warranty product along with the original
     *
     * @param Varien_Event_Observer $observer
     */
    public function addWarrantyProduct(Varien_Event_Observer $observer)
    {
        $originalProduct = $observer->getEvent()->getProduct();
        $warrantyProduct = null;

        try {
            /**
             * Add warranty products equal to the amount of original product added to cart.
             */
            if (Mage::helper('mulberry_warranty')->isActive()) {
                /**
                 * @var Mage_Catalog_Model_Product $originalProduct
                 * @var Mage_Sales_Model_Quote $quote
                 * @var Mage_Sales_Model_Quote_Item $originalQuoteItem
                 */
                $originalQuoteItem = $observer->getEvent()->getQuoteItem();
                $params = Mage::app()->getRequest()->getParams();
                $quote = $originalQuoteItem->getQuote();
                $warrantyProductsToAdd = isset($params['qty']) ? $params['qty'] : 1;

                if (array_key_exists('warranty', $params)) {
                    $warrantySku = $params['warranty']['sku'];
                    $isValidWarrantyHash = false;

                    /**
                     * Check whether we need to add warranty for this product or not
                     */
                    if (isset($params['warranty']['hash']) && !empty($params['warranty']['hash']) && $warrantySku === $this->getSelectedProductSku($originalProduct)) {
                        $isValidWarrantyHash = true;
                    }

                    /**
                     * Process additional warranty product add-to-cart
                     */
                    if ($originalProduct && $originalProduct->getId() && $isValidWarrantyHash) {
                        $warrantyHash = $params['warranty']['hash'];
                        $itemOptionHelper = Mage::helper('mulberry_warranty/item_option_helper');
                        $warrantyItemUpdater = Mage::helper('mulberry_warranty/item_updater');

                        /**
                         * Prepare buyRequest and other options for warranty quote item
                         */
                        $options = $itemOptionHelper->prepareWarrantyOption($originalQuoteItem, $warrantyHash);
                        $warrantyOptions = $itemOptionHelper->prepareWarrantyInformation($warrantyHash);

                        /**
                         * @var Mage_Catalog_Model_Product $warrantyProduct
                         */
                        $warrantyProduct = $this->getWarrantyPlaceholderProduct($warrantyOptions);

                        $warrantyItemUpdater->addWarrantyItemOption($warrantyProduct, $options);
                        $warrantyItemUpdater->addAdditionalOptions($warrantyProduct, $warrantyOptions);

                        /**
                         * Quote add to cart logic should be there to avoid duplicate in add-to-cart functionality
                         */
                        $options['qty'] = $warrantyProductsToAdd;
                        $warrantyQuoteItem = $quote->addProduct(
                            $warrantyProduct,
                            $this->getProductRequest($options)
                        );

                        /**
                         * Custom price should be set after quote item has been prepared
                         */
                        if (is_string($warrantyQuoteItem)) {
                            throw new Mage_Core_Exception(sprintf('%s, request %s', $warrantyQuoteItem, $this->getProductRequest($options)->toJson()));
                        }

                        $warrantyItemUpdater->setCustomWarrantyItemPrice($warrantyQuoteItem, $options);

                        $message = Mage::helper('mulberry_warranty')->__(
                            '%s was added to your shopping cart.',
                            $warrantyItemUpdater->prepareWarrantyProductName($warrantyQuoteItem)
                        );

                        $this->_getSession()->addSuccess($message);
                    }
                }
            }
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);

            $this->_getSession()->addError(
                Mage::helper('mulberry_warranty')->__('We were not able to add the warranty product to cart, but we did add the %s to cart',
                    $originalProduct->getName()
                )
            );
        }
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return string
     */
    private function getSelectedProductSku(Mage_Catalog_Model_Product $product)
    {
        return $product->getSku();
    }

    /**
     * @param array $requestInfo
     *
     * @return Varien_Object
     * @throws Mage_Core_Exception
     */
    private function getProductRequest(array $requestInfo = array())
    {
        if ($requestInfo instanceof Varien_Object) {
            $request = $requestInfo;
        } elseif (is_numeric($requestInfo)) {
            $request = new Varien_Object(array('qty' => $requestInfo));
        } elseif (is_array($requestInfo)) {
            $request = new Varien_Object($requestInfo);
        } else {
            throw new Mage_Core_Exception(
                Mage::helper('mulberry_warranty')->__('We found an invalid request for adding product to quote.')
            );
        }

        return $request;
    }

    /**
     * Retrieve Magento placeholder product to be used as a warranty product
     *
     * @param array $warrantyOptions
     * @return Mage_Core_Model_Abstract|Mage_Catalog_Model_Product
     */
    protected function getWarrantyPlaceholderProduct($warrantyOptions = array())
    {
        $placeholderSku = (is_array($warrantyOptions) && isset($warrantyOptions['duration_months'])) ? sprintf('mulberry-warranty-%s-months', $warrantyOptions['duration_months']) : 'mulberry-warranty-product';

        $productModel = Mage::getModel('catalog/product');

        return Mage::getModel('catalog/product')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($productModel->getIdBySku($placeholderSku));
    }

    /**
     * Get catalog session model instance
     *
     * @return Mage_Catalog_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('catalog/session');
    }

    /**
     * Set custom quote item name for warranty products
     *
     * @param Varien_Event_Observer $observer
     */
    public function setWarrantyProductName(Varien_Event_Observer $observer)
    {
        /**
         * @var Mage_Sales_Model_Quote_Item $quoteItem
         */
        $quoteItem = $observer->getEvent()->getQuoteItem();

        if ($quoteItem->getProductType() === Mulberry_Warranty_Model_Product_Type_Warranty::TYPE_ID) {
            Mage::helper('mulberry_warranty/item_updater')->updateWarrantyProductName($quoteItem);
        }
    }
}
