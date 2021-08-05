<?php
/**
 * @category Mulberry
 * @package Mulberry_Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2021 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

class Mulberry_Warranty_Model_Resource_Queue extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('mulberry_warranty/queue', 'entity_id');
    }
}
