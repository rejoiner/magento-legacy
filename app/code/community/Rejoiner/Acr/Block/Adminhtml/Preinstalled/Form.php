<?php

class Rejoiner_Acr_Block_Adminhtml_Preinstalled_Form extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    protected $_sourceRenderer;

    protected function _prepareToRender()
    {
        $this->addColumn('attr_name', array(
            'label' => Mage::helper('adminhtml')->__('Attribute Name'),
            'style' => 'width:120px',
            'renderer' => $this->_getSourceRenderer()
        ));
        $this->addColumn('value', array(
            'label' => Mage::helper('adminhtml')->__('Value'),
            'style' => 'width:120px',
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add Attribute');
    }

    /**
     * @return Mage_Core_Block_Abstract|Rejoiner_Acr_Block_Adminhtml_Form_Field_Source
     */
    protected function _getSourceRenderer()
    {
        if (!$this->_sourceRenderer) {
            $this->_sourceRenderer = $this->getLayout()->createBlock(
                'rejoiner_acr/adminhtml_form_field_source', 'google_anal',
                array('is_render_to_js_template' => true)
            );
        }
        return $this->_sourceRenderer;
    }

    /**
     * @param Varien_Object $row
     */
    protected function _prepareArrayRow(Varien_Object $row)
    {
        /** @var Rejoiner_Acr_Block_Adminhtml_Form_Field_Source $sourceRenderer */
        $sourceRenderer = $this->_getSourceRenderer();
        $row->setData(
            'option_extra_attr_' . $sourceRenderer->calcOptionHash($row->getData('attr_name')),
            'selected="selected"'
        );
    }
}