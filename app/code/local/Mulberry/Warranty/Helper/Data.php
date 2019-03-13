<?php

/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2018 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */
class Mulberry_Warranty_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * General Settings
     */
    const XML_PATH_IS_ACTIVE = 'mulberry_warranty/general/active';
    const XML_PATH_API_URL = 'mulberry_warranty/general/api_url';
    const XML_PATH_PARTNER_URL = 'mulberry_warranty/general/partner_url';
    const XML_PATH_PLATFORM_DOMAIN = 'mulberry_warranty/general/platform_domain';
    const XML_PATH_RETAILER_ID = 'mulberry_warranty/general/retailer_id';
    const XML_PATH_API_TOKEN = 'mulberry_warranty/general/api_token';
    const XML_PATH_SEND_CART_DATA = 'mulberry_warranty/general/send_cart_data';

    /**
     * Return flag if Mulberry warranty functionality is enabled
     *
     * @return bool
     */
    public function isActive()
    {
        return (bool) Mage::getStoreConfig(self::XML_PATH_IS_ACTIVE);
    }

    /**
     * Retrieve API URL required for Mulberry JS requests
     *
     * @return string
     */
    public function getApiUrl()
    {
        return Mage::getStoreConfig(self::XML_PATH_API_URL);
    }

    /**
     * Mulberry partner URL
     *
     * @return null|string
     */
    public function getPartnerUrl()
    {
        return Mage::getStoreConfig(self::XML_PATH_PARTNER_URL);
    }

    /**
     * Mulberry platform domain name used within API
     *
     * @return null|string
     */
    public function getPlatformDomain()
    {
        return Mage::getStoreConfig(self::XML_PATH_PLATFORM_DOMAIN);
    }

    /**
     * Mulberry retailer ID for order payload
     *
     * @return null|string
     */
    public function getRetailerId()
    {
        return Mage::getStoreConfig(self::XML_PATH_RETAILER_ID);
    }

    /**
     * API auth token that is used for calls
     *
     * @return null|string
     */
    public function getApiToken()
    {
        return Mage::getStoreConfig(self::XML_PATH_API_TOKEN);
    }

    /**
     * System config flag whether it's required to send tracking cart data on order place
     *
     * @return mixed
     */
    public function isSendCartDataEnabled()
    {
        return Mage::getStoreConfig(self::XML_PATH_SEND_CART_DATA);
    }
}
