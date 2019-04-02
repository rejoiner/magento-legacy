<?php

class Rejoiner_Acr_Block_Adminhtml_Form_Field_Salesrule extends Mage_Core_Block_Html_Select
{
    protected function _getSalesrules()
    {
        $salesruleSource = Mage::getModel('rejoiner_acr/system_config_source_salesrule');
        return $salesruleSource->toOptionArray();
        
    }

    /**
     * @param $value
     * @return mixed
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    public function _toHtml()
    {
        if (!$this->getOptions()) {
            foreach ($this->_getSalesrules() as $rule) {
                $this->addOption($rule['value'], Mage::helper('adminhtml')->__(addslashes($rule['label'])));
            }
        }
        return parent::_toHtml();
    }
}