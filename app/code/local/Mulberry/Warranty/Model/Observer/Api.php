<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2019 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

class Mulberry_Warranty_Model_Observer_Api
{
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

    /**
     * Send order information to Mulberry
     *
     * @param Varien_Event_Observer $observer
     */
    public function sendCart(Varien_Event_Observer $observer)
    {
        /**
         * @var Mage_Sales_Model_Order $order
         */
        $order = $observer->getEvent()->getOrder();

        if (Mage::helper('mulberry_warranty')->isActive()) {
            Mage::getModel('mulberry_warranty/api_rest_send_cart')->sendCart($order);
        }
    }

    /**
     * Generate and assign custom order identifier when placing an order
     *
     * @param Varien_Event_Observer $observer
     */
    public function generateOrderUuid(Varien_Event_Observer $observer)
    {
        /**
         * @var $order Mage_Sales_Model_Order
         * @var $uuidGenerator Mulberry_Warranty_Helper_Uuid_Generator
         */
        $order = $observer->getEvent()->getOrder();
        $uuidGenerator = Mage::helper('mulberry_warranty/uuid_generator');

        $order->setOrderIdentifier($uuidGenerator->uuid4()->toString());
    }
}
