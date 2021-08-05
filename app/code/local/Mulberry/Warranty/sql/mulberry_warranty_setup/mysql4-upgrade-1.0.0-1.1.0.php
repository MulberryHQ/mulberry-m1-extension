<?php
/**
 * @category Mulberry
 * @package Mulberry_Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2021 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

/* @var $installer Mage_Eav_Model_Entity_Setup */
$installer = $this;

$installer->startSetup();

$orderTableColumn = $installer->getConnection()->tableColumnExists($installer->getTable('sales/order'), 'order_identifier');
$orderGridTableColumn = $installer->getConnection()->tableColumnExists($installer->getTable('sales/order_grid'), 'order_identifier');

if (!$orderTableColumn) {
    $installer->getConnection()
        ->addColumn(
            $installer->getTable('sales/order'),
            'order_identifier',
            array(
                'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
                'comment'  => 'Order Identifier',
                'nullable' => true,
                'default'  => null,
                'length' => 30,
            )
        );
}

if (!$orderGridTableColumn) {
    $installer->getConnection()
        ->addColumn(
            $installer->getTable('sales/order_grid'),
            'order_identifier',
            array(
                'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
                'comment'  => 'Order Identifier',
                'nullable' => true,
                'default'  => null,
                'length' => 30,
            )
        );

    /**
     * Add index
     */
    $installer->getConnection()->addIndex(
        $installer->getTable('sales/order_grid'),
        $installer->getIdxName(
            'sales/order_grid',
            array('order_identifier'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('order_identifier'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    );
}

$installer->endSetup();
