<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @version 1.1.0
 * @copyright Copyright (c) 2019 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

class Mulberry_Warranty_Block_Adminhtml_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid
{
    /**
     * Add order_identifier columns in order grid
     * This is required in order to add order identifier field in Magento order CSV/Excel export
     *
     * @return $this|void
     */
    protected function _prepareColumns()
    {
        $this->addColumnAfter('order_identifier', array(
            'header' => Mage::helper('sales')->__('Order Identifier'),
            'index' => 'order_identifier',
        ), 'shipping_name');

        parent::_prepareColumns();
    }
}
