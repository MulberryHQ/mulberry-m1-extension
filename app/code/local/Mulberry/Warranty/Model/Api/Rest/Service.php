<?php
/**
 * @category Mulberry
 * @package Mulberry_Warranty
 * @author Mulberry <support@getmulberry.com>
 * @version 1.0.0
 * @copyright Copyright (c) 2021 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

class Mulberry_Warranty_Model_Api_Rest_Service
{
    /**
     * @var array $headers
     */
    private $headers = array();

    /**
     * @var null|string
     */
    private $uri;

    /**
     * Mulberry_Warranty_Model_Api_Rest_Service constructor.
     */
    public function __construct()
    {
        $this->uri = Mage::helper('mulberry_warranty')->getPartnerUrl();
    }

    /**
     * @param $url
     * @param string $body
     * @param string $method
     *
     * @return array|mixed|string
     */
    public function makeRequest($url, $body = '', $method = Zend_Http_Client::GET)
    {
        $response = array(
            'is_successful' => false,
        );

        try {
            $this->setHeader('Content-Type', 'application/json');
            $this->setHeader('Authorization', sprintf('Bearer %s', Mage::helper('mulberry_warranty')->getApiToken()));

            if (!$this->uri) {
                throw new Mage_Core_Exception(__('Partner URL setting is not set'));
            }

            $curl = new Varien_Http_Adapter_Curl();
            $curl->setConfig(array(
                'timeout'   => 5,
                'connect_timeout' => 5
            ));

            $curl->write($method, $this->uri . $url, '1.1', $this->headers, Mage::helper('core')->jsonEncode($body));

            $response = Zend_Http_Response::fromString($curl->read());
            $curl->close();

            $response = $this->processResponse($response);

            $response['is_successful'] = true;
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            $this->logRequestResponse($body, $response, $url);
        }

        return $response;
    }

    /**
     * @param string      $header
     * @param string|null $value
     */
    public function setHeader($header, $value = null)
    {
        if (!$value) {
            unset($this->headers[$header]);
            return;
        }

        $this->headers[$header] = sprintf('%s: %s', $header, $value);
    }

    /**
     * Process the response and return an array
     *
     * @param $response
     *
     * @return array|mixed
     */
    private function processResponse($response)
    {
        $data = array();

        if (is_array($response)) {
            return $response;
        }

        try {
            $data['result'] = Mage::helper('core')->jsonDecode((string)$response->getBody());
        } catch (\Exception $e) {
            $data = array(
                'exception' => $e->getMessage(),
            );
        }

        $data['response_object'] = array(
            'response' => $response,
        );

        return $data;
    }

    /**
     * @param $request
     * @param $response
     * @param $url
     */
    private function logRequestResponse($request, $response, $url)
    {
        $req = array(
            'headers' => $this->headers,
            'body' => $request,
        );

        Mage::log(array('REQUEST' => $req, 'action' => $url), Zend_Log::DEBUG);
        Mage::log(array('RESPONSE' => $response, 'action' => $url), Zend_Log::DEBUG);
    }
}
