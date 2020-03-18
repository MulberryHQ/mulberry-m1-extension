<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @version 1.0.0
 * @copyright Copyright (c) 2018 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

class Mulberry_Warranty_Model_Api_Rest_Warranty
{
    /**
     * Endpoint URI for warranty validation
     */
    const WARRANTY_VALIDATE_ENDPOINT_URL = '/api/validate_warranty/%s';

    /**
     * @var Mulberry_Warranty_Model_Api_Rest_Service
     */
    private $service;

    /**
     * Data mapping for warranty attributes,
     * stored as follows:
     * Magento additional information key => ['Mulberry API key']
     *
     * @var array $warrantyAttributesMapping
     */
    protected $warrantyAttributesMapping = array(
        'warranty_price' => array('cost'),
        'service_type' => array('service_type'),
        'warranty_hash' => array('warranty_hash'),
        'duration_months' => array('duration_months'),
    );

    /**
     * Mulberry_Warranty_Model_Api_Rest_Warranty constructor.
     */
    public function __construct()
    {
        $this->service = Mage::getModel('mulberry_warranty/api_rest_service');
    }

    /**
     * Retrieve warranty information from API using hash identifier
     *
     * @param string $hash
     *
     * @return mixed
     */
    public function getWarrantyByHash(string $hash)
    {
        $response = $this->service->makeRequest(sprintf(self::WARRANTY_VALIDATE_ENDPOINT_URL, $hash));

        return $this->parseResponse($response);
    }

    /**
     * Prepare data mapping for warranty product by hash
     *
     * @param $response
     *
     * @return array
     */
    private function parseResponse($response)
    {
        $result = array();

        /**
         * Warranty product information is stored in $response[0][0]
         */
        $warrantyProduct = (is_array($response) && isset($response['result'][0][0])) ? $response['result'][0][0] : array();

        if (!empty($warrantyProduct) && $this->validateWarrantyProductResponse($warrantyProduct)) {
            $result = array(
                'warranty_price' => (float) $warrantyProduct['cost'],
                'service_type' => $warrantyProduct['service_type'],
                'warranty_hash' => $warrantyProduct['warranty_hash'],
                'duration_months' => $warrantyProduct['duration_months'],
            );
        }

        return $result;
    }

    /**
     * Make sure we have all the necessary information
     *
     * @param $warrantyProduct
     *
     * @return bool
     */
    private function validateWarrantyProductResponse($warrantyProduct)
    {
        foreach ($this->warrantyAttributesMapping as $magentoAttribute => $apiNode) {
            $warrantyAttributeNode = $warrantyProduct;

            foreach ($apiNode as $node) {
                if (!isset($warrantyAttributeNode[$node])) {
                    return false;
                } else {
                    $warrantyAttributeNode = $warrantyAttributeNode[$node];
                }
            }
        }

        return true;
    }
}
