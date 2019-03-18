<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @version 1.0.0
 * @copyright Copyright (c) 2018 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

class Mulberry_Warranty_Model_Api_Rest_Get_Personalized_Warranty
{
    /**
     * Endpoint URI for warranty validation
     */
    const GET_WARRANTY_ENDPOINT_URL = '/api/get_personalized_warranty';

    /**
     * @var Mulberry_Warranty_Model_Api_Rest_Service
     */
    private $service;

    /**
     * Mulberry_Warranty_Model_Api_Rest_Get_Personalized_Warranty constructor.
     */
    public function __construct()
    {
        $this->service = Mage::getModel('mulberry_warranty/api_rest_service');
    }

    /**
     * Proxy method to retrieve warranty products information from API
     *
     * @param array $payload
     *
     * @return string
     */
    public function getWarrantiesJson(array $payload = array())
    {
        $payload = $this->getPayload($payload);

        $response = $this->service->makeRequest(self::GET_WARRANTY_ENDPOINT_URL, $payload, Zend_Http_Client::POST);

        return Mage::helper('core')->jsonEncode($this->parseResponse($response));
    }

    /**
     * @param $response
     *
     * @return array|mixed
     */
    private function parseResponse($response)
    {
        return is_array($response) && isset($response['result']) ? $response['result'] : array();
    }

    /**
     * @param $payload
     *
     * @return mixed
     */
    private function getPayload(array $payload = array())
    {
        $payload['token'] = Mage::helper('mulberry_warranty')->getApiToken();

        return $payload;
    }
}
