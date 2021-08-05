<?php
/**
 * @category Mulberry
 * @package Mulberry_Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2021 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

class Mulberry_Warranty_Model_Cron
{
    /**
     * Process "Send Order" records
     */
    public function sendOrder()
    {
        if (Mage::helper('mulberry_warranty')->isActive()) {
            $collection = $this->getOrderCollection('order');
            $collection->setPageSize(20)
                ->setCurPage(1);

            Mage::log(
                Mage::helper('mulberry_warranty')->__('Starting SendOrder action processing. There are %1 records that will be processed',
                    $collection->getSize())
            );

            foreach ($collection as $order) {
                Mage::getModel('mulberry_warranty/queue')->process($order, 'order');
            }

            Mage::log('Cronjob SendOrder is finished.');
        }
    }

    /**
     * Process "Post Purchase" records
     */
    public function sendCart()
    {
        if (Mage::helper('mulberry_warranty')->isActive()) {
            $collection = $this->getOrderCollection('cart');
            $collection->setPageSize(20)
                ->setCurPage(1);

            Mage::log(
                Mage::helper('mulberry_warranty')->__('Starting SendCart action processing. There are %1 records that will be processed',
                    $collection->getSize())
            );

            foreach ($collection as $order) {
                Mage::getModel('mulberry_warranty/queue')->process($order, 'cart');
            }

            Mage::log('Cronjob SendOrder is finished.');
        }
    }

    /**
     * @param $actionType
     * @return mixed
     */
    private function getOrderCollection($actionType)
    {
        $orderCollection = Mage::getModel('sales/order')->getCollection();
        $this->joinQueueToOrderCollection($orderCollection);
        $this->addSyncStatusFilter($orderCollection, null);
        $this->addActionTypeFilter($orderCollection, $actionType);

        return $orderCollection;
    }

    /**
     * @param $collection
     * @return Mulberry_Warranty_Model_Cron
     */
    private function joinQueueToOrderCollection($collection)
    {
        $collection->getSelect()->joinLeft(
            array('mwq' => 'mulberry_warranty_queue'),
            'main_table.entity_id = mwq.order_id',
            array('action_type', 'sync_status', 'sync_date')
        );

        return $this;
    }

    /**
     * @param $collection
     * @param $syncStatus
     * @return Mulberry_Warranty_Model_Cron
     */
    private function addSyncStatusFilter($collection, $syncStatus)
    {
        $collection->getSelect()->where(
            $syncStatus === null ? 'mwq.sync_status IS NULL' : 'mwq.sync_status = ?',
            $syncStatus
        );

        return $this;
    }

    /**
     * Filter order collection by the action type
     *
     * @param $collection
     * @param $actionType
     * @return Mulberry_Warranty_Model_Cron
     */
    private function addActionTypeFilter($collection, $actionType)
    {
        $collection->getSelect()->where(
            'mwq.action_type = ?',
            $actionType
        );

        return $this;
    }
}
