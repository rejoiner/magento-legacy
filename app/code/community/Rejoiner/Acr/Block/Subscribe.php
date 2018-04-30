<?php
class Rejoiner_Acr_Block_Subscribe extends Mage_Core_Block_Template
{
    /**
     * @inherit
     */
    protected function _toHtml()
    {
        if (Mage::getStoreConfig(Rejoiner_Acr_Helper_Data::XML_PATH_REJOINER_ENABLED) && Mage::getStoreConfig(Rejoiner_Acr_Helper_Data::XML_PATH_REJOINER_MARKETING_PERMISSIONS) && $this->getLayout()->getBlockSingleton('customer/form_register')->isNewsletterEnabled()) {
            if (Mage::getStoreConfig('checkout/rejoiner_acr/subscribe_'.$this->getSubscribePage())
                || $this->getSubscribePage() == Rejoiner_Acr_Helper_Data::PAGE_CUSTOMER_REGISTER) {
                return parent::_toHtml();
            }
        }
    }

}
