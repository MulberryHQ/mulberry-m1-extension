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

$warrantyTableExists = $installer->getConnection()->isTableExists('mulberry_warranty_queue');

if (!$warrantyTableExists) {
    $table = $installer->getConnection()
        ->newTable($installer->getTable('mulberry_warranty_queue'))
        ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ), 'Entity Id')
        ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
        ), 'Order Id')
        ->addColumn('action_type', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
            'nullable'  => true,
        ), 'Export Action Type')
        ->addColumn('sync_status', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
            'nullable'  => true,
        ), 'Mulberry Order Sync Status')
        ->addColumn('sync_date', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
            'nullable'  => true,
        ), 'Mulberry Order Sync Date')
        ->addForeignKey($installer->getFkName('mulberry_warranty_queue', 'order_id', 'sales_flat_order', 'entity_id'),
            'order_id', $installer->getTable('sales_flat_order'), 'entity_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE)
        ->setComment('Mulberry Warranty Queue');
    $installer->getConnection()->createTable($table);
}

$installer->endSetup();
