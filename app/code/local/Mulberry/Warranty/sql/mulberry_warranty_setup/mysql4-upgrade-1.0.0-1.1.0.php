<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    Phoenix
 * @package     Phoenix_Moneybookers
 * @copyright   Copyright (c) 2018 Phoenix Medien GmbH & Co. KG (http://www.phoenix-medien.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
