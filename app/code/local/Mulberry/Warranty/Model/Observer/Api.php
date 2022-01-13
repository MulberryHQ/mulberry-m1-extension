<?php
/**
 * @category Mulberry
 * @package Mulberry_Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2021 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

class Mulberry_Warranty_Model_Observer_Api
{
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

    /**
     * Add order to warranty export queue when placing an order
     *
     * @param Varien_Event_Observer $observer
     */
    public function addToQueue(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        if (Mage::helper('mulberry_warranty')->isActive()) {
            /**
             * Add order to "send cart" queue if it's enabled
             */
            if (Mage::helper('mulberry_warranty')->isSendCartDataEnabled()) {
                $cartQueue = Mage::getModel('mulberry_warranty/queue');
                $cartQueue->setOrderId($order->getId());
                $cartQueue->setActionType('cart');
                $cartQueue->save();
            }

            /**
             * Add order to queue if there's an extended warranty purchased
             */
            if ($this->orderHasWarrantyItems($order)) {
                $orderQueue = Mage::getModel('mulberry_warranty/queue');
                $orderQueue->setOrderId($order->getId());
                $orderQueue->setActionType('order');
                $orderQueue->save();
            }
        }
    }

    /**
     * Prepare payload for order items
     * @param $order
     * @return bool
     */
    private function orderHasWarrantyItems($order)
    {
        foreach ($order->getAllItems() as $item) {
            if ($item->getProductType() === Mulberry_Warranty_Model_Product_Type_Warranty::TYPE_ID) {
                return true;
            }
        }

        return false;
    }
}
