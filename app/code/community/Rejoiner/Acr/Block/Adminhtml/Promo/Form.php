<?php

class Rejoiner_Acr_Block_Adminhtml_Promo_Form extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    protected $_salesruleRenderer;
    protected $_promotypeRenderer;

    protected function _prepareToRender()
    {
      $this->addColumn('promo_param', array(
          'label' => Mage::helper('adminhtml')->__('Parameter Name'),
          'style' => 'width:120px'
      ));
      $this->addColumn('promo_salesrule', array(
          'label' => Mage::helper('adminhtml')->__('Sales Rule'),
          'renderer' => $this->_getSalesruleRenderer()
      ));
      $this->_addAfter = false;
      $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add Code');
    }

    protected function _getSalesruleRenderer()
    {
        if (!$this->_salesruleRenderer) {
            $this->_salesruleRenderer = $this->getLayout()->createBlock(
                'rejoiner_acr/adminhtml_form_field_salesrule', 'promo_salesrule',
                array('is_render_to_js_template' => true)
            );
        } 
        return $this->_salesruleRenderer;
    }

    protected function _prepareArrayRow(Varien_Object $row)
    {
        $salesruleRenderer = $this->_getSalesruleRenderer();
        $row->setData(
            'option_extra_attr_' . $salesruleRenderer->calcOptionHash($row->getData('promo_salesrule')),
            'selected="selected"'
        );
    }
}