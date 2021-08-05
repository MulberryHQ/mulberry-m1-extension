<?php
/**
 * @category Mulberry
 * @package Mulberry_Warranty
 * @author Mulberry <support@getmulberry.com>
 * @version 1.0.0
 * @copyright Copyright (c) 2021 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

class Mulberry_Warranty_Model_Api_Rest_Send_Order
{
    /**
     * Endpoint URI for sending order information
     */
    const ORDER_SEND_ENDPOINT_URL = '/api/checkout';

    /**
     * @var Mulberry_Warranty_Model_Api_Rest_Service
     */
    private $service;

    /**
     * @var bool $orderHasWarrantyProducts
     */
    private $orderHasWarrantyProducts = false;

    /**
     * @var array $warrantyItemsPayload
     */
    private $warrantyItemsPayload = array();

    /**
     * @var Mage_Sales_Model_Order $order
     */
    private $order;

    /**
     * Data mapping for warranty attributes,
     * stored as follows:
     * Magento additional information key => ['Mulberry API key']
     *
     * @var array $warrantyAttributesMapping
     */
    protected $warrantyAttributesMapping = array(
        'warranty_price' => array('cost'),
        'service_type' => array('service_type'),
        'warranty_hash' => array('warranty_hash'),
        'duration_months' => array('duration_months'),
        'product_name' => array('product', 'name'),
    );

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
    public function sendOrder(Mage_Sales_Model_Order $order)
    {
        if (!Mage::helper('mulberry_warranty')->isActive()) {
            return array();
        }

        $this->order = $order;
        $this->prepareItemsPayload();

        if (!$this->orderHasWarrantyProducts) {
            return array();
        }

        $emulationModel = Mage::getSingleton('core/app_emulation');

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
        /**
         * @var $item Mage_Sales_Model_Order_Item
         */
        foreach ($this->order->getAllItems() as $item) {
            if ($item->getProductType() === Mulberry_Warranty_Model_Product_Type_Warranty::TYPE_ID) {
                $this->orderHasWarrantyProducts = true;
                $this->prepareItemPayload($item);
            }
        }
    }

    /**
     * @return array
     */
    private function getOrderPayload()
    {
        $order = $this->order;

        $payload = array(
            'id' => $order->getIncrementId(),
            'phone' => $order->getBillingAddress()->getTelephone(),
            'email' => $order->getCustomerEmail(),
            'retailer_id' => Mage::helper('mulberry_warranty')->getRetailerId(),
            'cart_token' => $order->getOrderIdentifier(),
            'billing_address' => $this->prepareAddressData(),
            'line_items' => $this->warrantyItemsPayload,
        );

        return $payload;
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
            'address2' => $billingAddress->getStreet(2),
            'city' => $billingAddress->getCity(),
            'state' => $billingAddress->getRegionCode(),
            'zip' => $billingAddress->getPostcode(),
            'country' => Mage::getModel('directory/country')->loadByCode($billingAddress->getCountryId())->getName(),
            'country_code' => $billingAddress->getCountryId(),
            'province_code' => $billingAddress->getRegionCode(),
        );
    }

    /**
     * Prepare payload single warranty item,
     * payload should contain separate object for each item purchased (no qty support in API at the moment)
     *
     * @param Mage_Sales_Model_Order_Item $item
     */
    private function prepareItemPayload(Mage_Sales_Model_Order_Item $item)
    {
        $warrantyProductData = $item->getBuyRequest()->getWarrantyProduct();
        $originalProductData = $item->getBuyRequest()->getOriginalProduct();

        for ($i = 0; $i < (int) $item->getQtyOrdered(); $i++) {
            $this->warrantyItemsPayload[] = array(
                'product_id' => $originalProductData['product_sku'],
                'product_price' => $item->getPrice(),
                'product_title' => $item->getName(),
                'warranty_hash' => $warrantyProductData['warranty_hash'],
            );
        }
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
