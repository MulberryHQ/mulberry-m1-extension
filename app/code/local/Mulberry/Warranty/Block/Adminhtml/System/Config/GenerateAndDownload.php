<?php
/**
 * @category Mulberry
 * @package Mulberry_Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2021 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

class Mulberry_Warranty_Block_Adminhtml_System_Config_GenerateAndDownload
    extends Mage_Adminhtml_Block_System_Config_Form_Field
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_template = 'warranty/system/config/generateAndDownload.phtml';

    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_toHtml();
    }

    /**
     * Return ajax url for generate and download button
     *
     * @return string
     */
    public function getGenerateAndDownloadAjaxUrl()
    {
        return Mage::getModel('adminhtml/url')->getUrl('adminhtml/system_config_export/generate');
    }

    /**
     * Return ajax url for download button
     *
     * @return string
     */
    public function getDownloadAjaxUrl()
    {
        return Mage::getModel('adminhtml/url')->getUrl('adminhtml/system_config_export/download');
    }


    /**
     * Generate collect button html
     *
     * @return string
     */
    public function getGenerateAndDownloadButtonHtml()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
            'id' => 'generate_and_download_button',
            'label' => Mage::helper('mulberry_warranty')->__('Generate and Download')
        ));

        return $button->toHtml();
    }

    /**
     * Generate collect button html
     *
     * @return string
     */
    public function getDownloadButtonHtml()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
            'id' => 'download_button',
            'label' => Mage::helper('mulberry_warranty')->__('Download')
        ));

        return $button->toHtml();
    }
}
