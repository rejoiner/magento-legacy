<?php
/**
 * Class Rejoiner_Acr_Block_Adminhtml_Form_Field_Source
 * @method setName(string $name)
 */
class Rejoiner_Acr_Block_Adminhtml_Form_Field_Source extends Mage_Core_Block_Html_Select
{
    protected $_metaSources = array(
        'utm_source' =>  'Campaign Source',
        'utm_medium'  =>  'Campaign Medium',
        'utm_campaign' =>  'Campaign Name',
    );

    protected $_addGroupAllOption = true;


    /**
     * @return array
     */
    protected function _getMetaSources()
    {
        return $this->_metaSources;
    }

    /**
     * @param $value
     * @return mixed
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            foreach ($this->_getMetaSources() as $groupId => $groupLabel) {
                $this->addOption($groupId, Mage::helper('adminhtml')->__(addslashes($groupLabel)));
            }
        }
        return parent::_toHtml();
    }
}
