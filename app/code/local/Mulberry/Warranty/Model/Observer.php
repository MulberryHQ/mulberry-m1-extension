<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Dmitrijs Sitovs <info@scandiweb.com / dmitrijssh@scandiweb.com / dsitovs@gmail.com>
 * @copyright Copyright (c) 2018 Scandiweb, Ltd (http://scandiweb.com)
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
        try {
            /**
             * @var Mage_Catalog_Model_Product $warrantyProduct
             */
            $warrantyProduct = $this->getWarrantyPlaceholderProduct();

            /**
             * Add warranty products equal to the amount of original product added to cart.
             */
            if (Mage::helper('mulberry_warranty')->isActive() && $warrantyProduct->getId()) {
                /**
                 * @var Mage_Catalog_Model_Product $originalProduct
                 * @var Mage_Sales_Model_Quote $quote
                 * @var Mage_Sales_Model_Quote_Item $originalQuoteItem
                 */
                $originalProduct = $observer->getEvent()->getProduct();
                $originalQuoteItem = $observer->getEvent()->getQuoteItem();
                $params = Mage::app()->getRequest()->getParams();
                $quote = $originalQuoteItem->getQuote();
                $warrantyProductsToAdd = isset($params['qty']) ? $params['qty'] : 1;

                if (array_key_exists('warranty', $params)) {
                    /**
                     * Check whether we need to add warranty for this product or not
                     */
                    if (isset($params['warranty'][$this->getSelectedProductSku($originalProduct)])
                        && !empty($params['warranty'][$this->getSelectedProductSku($originalProduct)])) {
                        $warrantyHash = $params['warranty'][$this->getSelectedProductSku($originalProduct)];
                    } else {
                        $warrantyHash = false;
                    }

                    /**
                     * Process additional warranty product add-to-cart
                     */
                    if ($originalProduct && $originalProduct->getId() && $warrantyHash) {
                        $itemOptionHelper = Mage::helper('mulberry_warranty/item_option_helper');
                        $warrantyItemUpdater = Mage::helper('mulberry_warranty/item_updater');

                        /**
                         * Prepare buyRequest and other options for warranty quote item
                         */
                        $options = $itemOptionHelper->prepareWarrantyOption($originalQuoteItem, $warrantyHash);
                        $warrantyOptions = $itemOptionHelper->prepareWarrantyInformation($warrantyHash);

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
                        $warrantyItemUpdater->setCustomWarrantyItemPrice($warrantyQuoteItem, $options);
                    }
                }
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
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
    private function getProductRequest(array $requestInfo = [])
    {
        if ($requestInfo instanceof Varien_Object) {
            $request = $requestInfo;
        } elseif (is_numeric($requestInfo)) {
            $request = new Varien_Object(['qty' => $requestInfo]);
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
     * @return Mage_Core_Model_Abstract|Mage_Catalog_Model_Product
     */
    protected function getWarrantyPlaceholderProduct()
    {
        $productModel = Mage::getModel('catalog/product');

        return Mage::getModel('catalog/product')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($productModel->getIdBySku('mulberry-warranty-product'));
    }

    /**
     * Get checkout session model instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
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

    /**
     * Send order information to Mulberry
     *
     * @param Varien_Event_Observer $observer
     */
    public function sendOrder(Varien_Event_Observer $observer)
    {
        /**
         * @var Mage_Sales_Model_Order $order
         */
        $order = $observer->getEvent()->getOrder();

        if (Mage::helper('mulberry_warranty')->isActive()) {
            Mage::getModel('mulberry_warranty/api_rest_send_order')->sendOrder($order);
        }
    }

    /**
     * Perform order cancellation for Mulberry warranty products
     *
     * @param Varien_Event_Observer $observer
     */
    public function cancelOrder(Varien_Event_Observer $observer)
    {
        /**
         * @var Mage_Sales_Model_Order $order
         */
        $order = $observer->getEvent()->getOrder();

        if (Mage::helper('mulberry_warranty')->isActive()) {
            Mage::getModel('mulberry_warranty/api_rest_cancel_order')->cancelOrder($order);
        }
    }
}
