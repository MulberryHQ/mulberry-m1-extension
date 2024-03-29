<?php
/**
 * @category Mulberry
 * @package Mulberry_Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2021 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

class Mulberry_Warranty_Model_Api_Rest_Cancel_Order
{
    /**
     * Endpoint URI for sending order cancellation information
     */
    const ORDER_CANCEL_ENDPOINT_URL = '/api/order_cancelled';

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
     * Mulberry_Warranty_Model_Api_Rest_Cancel_Order constructor.
     */
    public function __construct()
    {
        $this->service = Mage::getModel('mulberry_warranty/api_rest_service');
    }

    /**
     * Send order cancellation payload to Mulberry system
     *
     * @param Mage_Sales_Model_Order $order
     * @return array
     * @throws Zend_Date_Exception
     */
    public function cancelOrder(Mage_Sales_Model_Order $order)
    {
        $this->order = $order;
        $this->prepareItemsPayload();

        if (!$this->orderHasWarrantyProducts) {
            return array();
        }

        $emulationModel = Mage::getModel('core/app_emulation');

        $payload = $this->getOrderCancellationPayload();
        $emulationModel->startEnvironmentEmulation($this->order->getStoreId(), Mage_Core_Model_App_Area::AREA_FRONTEND, true);
        $response = $this->service->makeRequest(self::ORDER_CANCEL_ENDPOINT_URL, $payload, Zend_Http_Client::POST);
        $emulationModel->stopEnvironmentEmulation();

        return $this->parseResponse($response);
    }

    /**
     * Prepare payload for order items
     */
    private function prepareItemsPayload()
    {
        /**
         * @var Mage_Sales_Model_Order_Item $item
         */
        foreach ($this->order->getAllItems() as $item) {
            if ($item->getProductType() === Mulberry_Warranty_Model_Product_Type_Warranty::TYPE_ID && $item->getQtyCanceled()) {
                $this->orderHasWarrantyProducts = true;
                $this->prepareItemPayload($item);
            }
        }
    }

    /**
     * Prepare full payload to be sent, when Magento order is cancelled
     *
     * @return array
     * @throws Zend_Date_Exception
     */
    private function getOrderCancellationPayload()
    {
        $date = new Zend_Date();

        $payload = array(
            'cancelled_date' => $date->toString('Y-m-d'),
            'order_id' => $this->order->getIncrementId(),
            'line_items' => $this->warrantyItemsPayload,
        );

        return $payload;
    }

    /**
     * Prepare cancellation payload for order item
     *
     * @param Mage_Sales_Model_Order_Item $item
     */
    private function prepareItemPayload(Mage_Sales_Model_Order_Item $item)
    {
        $originalProductData = $item->getBuyRequest()->getOriginalProduct();

        for ($i = 0; $i < (int) $item->getQtyCanceled(); $i++) {
            $this->warrantyItemsPayload[] = array(
                'product_id' => $originalProductData['product_sku'],
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
        return array();
    }
}
