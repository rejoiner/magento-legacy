<?php

class Rejoiner_Acr_Block_Adminhtml_Custom_Form extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct()
    {
        $this->addColumn('attr_name', array(
            'label' => Mage::helper('adminhtml')->__('Attribute Name'),
            'style' => 'width:120px',
        ));
        $this->addColumn('value', array(
            'label' => Mage::helper('adminhtml')->__('Value'),
            'style' => 'width:120px',
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add Attribute');
        parent::__construct();
    }
}