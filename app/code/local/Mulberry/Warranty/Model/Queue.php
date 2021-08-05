<?php
/**
 * @category Mulberry
 * @package Mulberry_Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2021 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

class Mulberry_Warranty_Model_Queue extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('mulberry_warranty/queue');
    }

    public function process($order, $actionType)
    {
        $queue = $this->getByOrderIdAndActionType($order->getId(), $actionType);

        if ($queue->getId()) {
            $actionType = $queue->getActionType();

            switch ($actionType) {
                case 'order':
                    $response = Mage::getModel('mulberry_warranty/api_rest_send_order')->sendOrder($order);
                    break;
                case 'cart':
                    $response = Mage::getModel('mulberry_warranty/api_rest_send_cart')->sendCart($order);
                    break;
                default:
                    $response = [
                        'status' => 'skipped',
                        'response' => Mage::helper('mulberry_warranty')->__('Invalid action type for order "#%1"', $order->getIncrementId())
                    ];
                    break;
            }

            if ($response['status'] !== 'synced') {
                Mage::log(json_encode($response));
            }

            $queue->setSyncStatus($response['status']);
            $queue->setSyncDate(time());
            $queue->save();
        }
    }

    /**
     * @param $orderId
     * @param $actionType
     * @return Mulberry_Warranty_Model_Queue
     */
    public function getByOrderIdAndActionType($orderId, $actionType)
    {
        $collection = $this->getCollection();

        $collection->addFieldToFilter('order_id', $orderId)
            ->addFieldToFilter('action_type', $actionType);

        return $collection->getSize() ? $collection->getFirstItem() : $this;
    }
}
