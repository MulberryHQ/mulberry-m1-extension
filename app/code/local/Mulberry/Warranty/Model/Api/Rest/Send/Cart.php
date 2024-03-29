<?php
/**
 * @category Mulberry
 * @package Mulberry_Warranty
 * @author Mulberry <support@getmulberry.com>
 * @version 1.0.0
 * @copyright Copyright (c) 2021 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

class Mulberry_Warranty_Model_Api_Rest_Send_Cart
{
    /**
     * Endpoint URI for sending order information
     */
    const ORDER_SEND_ENDPOINT_URL = '/api/carts';

    /**
     * @var Mulberry_Warranty_Model_Api_Rest_Service
     */
    private $service;

    /**
     * @var array $itemsPayload
     */
    private $itemsPayload = array();

    /**
     * @var Mage_Sales_Model_Order $order
     */
    private $order;

    /**
     * @var array
     */
    private $warrantyItems;

    /**
     * @var array
     */
    private $orderItems;

    /**
     * Mulberry_Warranty_Model_Api_Rest_Send_Order constructor.
     */
    public function __construct()
    {
        $this->service = Mage::getModel('mulberry_warranty/api_rest_service');
    }

    /**
     * Send order payload to Mulberry system
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return mixed
     */
    public function sendCart(Mage_Sales_Model_Order $order)
    {
        /**
         * @var $helper Mulberry_Warranty_Helper_Data
         */
        $helper = Mage::helper('mulberry_warranty');

        if (!$helper->isActive() || !$helper->isSendCartDataEnabled()) {
            return array();
        }

        $emulationModel = Mage::getSingleton('core/app_emulation');
        $this->order = $order;
        $this->prepareItemsPayload();

        /**
         * If there are no items to send, create dummy response and mark order as "synced"
         */
        if (empty($this->itemsPayload)) {
            $response = [
                'is_successful' => true,
                'response' => [
                    'message' => 'No items available to export',
                ],
            ];

            return $this->parseResponse($response);
        }

        $payload = $this->getOrderPayload();
        $initialEnvironmentInfo = $emulationModel->startEnvironmentEmulation($this->order->getStoreId());
        $response = $this->service->makeRequest(self::ORDER_SEND_ENDPOINT_URL, $payload, Zend_Http_Client::POST);
        $emulationModel->stopEnvironmentEmulation($initialEnvironmentInfo);

        return $this->parseResponse($response);
    }

    /**
     * Prepare payload for order items
     */
    private function prepareItemsPayload()
    {
        foreach ($this->order->getAllVisibleItems() as $item) {
            if ($item->getProductType() === Mulberry_Warranty_Model_Product_Type_Warranty::TYPE_ID) {
                $this->warrantyItems[] = [
                    'item' => $item,
                    'quantity' => (int) $item->getQtyOrdered(),
                ];
            } else {
                $this->orderItems[] = [
                    'item' => $item,
                    'quantity' => (int) $item->getQtyOrdered(),
                ];
            }
        }

        /**
         * Send only order items without the associated warranty item
         *
         * @var $item Mage_Sales_Model_Order_Item
         */
        foreach ($this->orderItems as $key => $itemDataArray) {
            $item = $itemDataArray['item'];

            for ($i = 0; $i < (int) $item->getQtyOrdered(); $i++) {
                if (!$this->isPostPurchaseEligible($itemDataArray, $key)) {
                    continue;
                }

                /**
                 * Add item for the post purchase payload
                 */
                $this->itemsPayload[] = $this->prepareItemPayload($item);
            }
        }
    }

    /**
     * Check if the item is eligible for the post purchase.
     *
     * @param array $itemDataArray
     * @param $key
     * @return bool
     */
    private function isPostPurchaseEligible(array $itemDataArray, $key)
    {
        $item = $itemDataArray['item'];

        /**
         * Exclude the warranty products from the payload
         */
        if ($item->getProductType() === Mulberry_Warranty_Model_Product_Type_Warranty::TYPE_ID) {
            return false;
        }

        /**
         * Exclude order items with the warranty purchased
         *
         * @var Mage_Sales_Model_Order_Item $warrantyItem
         */
        foreach ($this->warrantyItems as $key => $warrantyItemArray) {
            $warrantyItem = $warrantyItemArray['item'];
            $associatedProduct = $warrantyItem->getBuyRequest()->getOriginalProduct();
            $associatedSku = $associatedProduct['product_sku'];

            if ($item->getSku() === $associatedSku) {
                $warrantyItemArray['quantity'] = (int) $warrantyItemArray['quantity'] - 1;

                if ((int) $warrantyItemArray['quantity'] < 1) {
                    unset($this->warrantyItems[$key]);
                }

                $this->orderItems[$key]['quantity'] = (int) $this->orderItems[$key]['quantity'] - 1;

                return false;
            }
        }

        return true;
    }

    /**
     * @return array
     */
    private function getOrderPayload()
    {
        return array(
            'line_items' => $this->itemsPayload,
            'billing_address' => $this->prepareAddressData(),
            'order_id' => $this->order->getIncrementId(),
        );
    }

    /**
     * Retrieve billing address data for order payload
     *
     * @return array
     */
    private function prepareAddressData()
    {
        /**
         * @var $billingAddress Mage_Sales_Model_Order_Address
         */
        $billingAddress = $this->order->getBillingAddress();

        return array(
            'first_name' => $billingAddress->getFirstname(),
            'last_name' => $billingAddress->getLastname(),
            'address1' => $billingAddress->getStreet(1),
            'phone' => $billingAddress->getTelephone(),
            'email' => $billingAddress->getEmail(),
            'city' => $billingAddress->getCity(),
            'zip' => $billingAddress->getPostcode(),
            'state' => $billingAddress->getRegionCode(),
            'country' => Mage::getModel('directory/country')->loadByCode($billingAddress->getCountryId())->getName(),
            'address2' => $billingAddress->getStreet(2),
            'country_code' => $billingAddress->getCountryId(),
            'province_code' => $billingAddress->getRegionCode(),
        );
    }

    /**
     * Prepare payload single warranty item,
     * payload should contain separate object for each item purchased (no qty support in API at the moment)
     *
     * @param Mage_Sales_Model_Order_Item $item
     * @return array
     */
    private function prepareItemPayload(Mage_Sales_Model_Order_Item $item)
    {
        return array(
            'product_id' => $item->getSku(),
            'product_price' => $item->getPrice(),
            'product_title' => $item->getName(),
            'meta' => [
                'breadcrumbs' => Mage::helper('mulberry_warranty/product')->getProductBreadcrumbs($item->getProduct()),
            ],
            'images' => Mage::helper('mulberry_warranty/product')->getGalleryImagesInfo($item->getProduct()),
        );
    }

    /**
     * @param $response
     *
     * @return array
     */
    private function parseResponse($response)
    {
        return array(
            'status' => $response['is_successful'] ? 'synced' : 'failed',
            'response' => $response
        );
    }
}
