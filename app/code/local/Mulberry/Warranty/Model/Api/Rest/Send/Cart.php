<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @version 1.0.0
 * @copyright Copyright (c) 2018 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
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

        $this->order = $order;
        $this->prepareItemsPayload();

        $payload = $this->getOrderPayload();

        $response = $this->service->makeRequest(self::ORDER_SEND_ENDPOINT_URL, $payload, Zend_Http_Client::POST);

        return $this->parseResponse($response);
    }

    /**
     * Prepare payload for order items
     */
    private function prepareItemsPayload()
    {
        /**
         * @var $item Mage_Sales_Model_Order_Item
         */
        foreach ($this->order->getAllVisibleItems() as $item) {
            /**
             * We don't need to send warranty products as a payload
             */
            if ($item->getProductType() === Mulberry_Warranty_Model_Product_Type_Warranty::TYPE_ID) {
                continue;
            }

            $this->prepareItemPayload($item);
        }
    }

    /**
     * @return array
     */
    private function getOrderPayload()
    {
        return [
            'line_items' => $this->itemsPayload,
            'billing_address' => $this->prepareAddressData(),
            'order_id' => $this->order->getOrderIdentifier(),
        ];
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

        return [
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
        ];
    }

    /**
     * Prepare payload single warranty item,
     * payload should contain separate object for each item purchased (no qty support in API at the moment)
     *
     * @param Mage_Sales_Model_Order_Item $item
     */
    private function prepareItemPayload(Mage_Sales_Model_Order_Item $item)
    {
        for ($i = 0; $i < (int) $item->getQtyOrdered(); $i++) {
            $this->itemsPayload[] = [
                'product_id' => $item->getId(),
                'product_price' => $item->getPrice(),
                'product_title' => $item->getName(),
            ];
        }
    }

    /**
     * @param $response
     *
     * @return array
     */
    private function parseResponse($response)
    {
        return array();
    }
}
