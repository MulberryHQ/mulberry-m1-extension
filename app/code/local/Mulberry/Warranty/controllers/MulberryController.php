<?php

class Mulberry_Warranty_MulberryController extends Mage_Core_Controller_Front_Action
{
    /**
     *
     */
    public function get_personalized_warrantyAction()
    {
        try {
            $payload = $this->_preparePayload();
        } catch (\Zend_Json_Exception $e) {
            $payload = [];
        }

        $result = Mage::getModel('mulberry_warranty/api_rest_get_personalized_warranty')->getWarrantiesJson($payload);

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($result);
    }

    /**
     * @return false|Mage_Core_Model_Abstract|Mulberry_Warranty_Model_Api_Rest_Get_Personalized_Warranty
     */
    protected function _getWarrantyService()
    {
        return Mage::getModel('mulberry_warranty/api_rest_get_personalized_warranty');
    }

    /**
     * @param $response
     *
     * @return array|mixed
     */
    protected function _parseResponse($response)
    {
        return is_array($response) && isset($response['result']) ? $response['result'] : [];
    }

    /**
     * @param $payload
     *
     * @return mixed
     */
    protected function _getPayload(array $payload = [])
    {
        $payload['token'] = Mage::helper('mulberry_warranty')->getApiToken();

        return $payload;
    }

    /**
     * @return mixed
     */
    protected function _preparePayload()
    {
        $json = file_get_contents('php://input');

        return Mage::helper('core')->jsonDecode($json);
    }
}
