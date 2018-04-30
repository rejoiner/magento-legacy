<?php
/**
 * Block to check overriding for checkout by other extensions and show warning message
 *
 * @category   Rejoiner
 * @package    Rejoiner_Acr
 */
class Rejoiner_Acr_Block_Adminhtml_Notification extends Mage_Core_Block_Template
{
    /**
     * @return bool
     */
    public function canShow()
    {
        if (Rejoiner_Acr_Model_Notification::isNotificationViewed()) {
            return false;
        }
        return ($this->_isCoreCheckoutControllerOverridden() || $this->_isCoreCheckoutUrlHelperOverridden());
    }

    /**
     * @return bool
     */
    protected function _isCoreCheckoutControllerOverridden()
    {
        $frontController = new Mage_Core_Controller_Varien_Front();
        $frontController->init();

        /** @var Mage_Core_Controller_Varien_Router_Standard $standardRouter */
        $standardRouter = $frontController->getRouter('standard');
        $modules = $standardRouter->getModuleByFrontName('checkout');
        return reset($modules) != 'Mage_Checkout';
    }

    /**
     * @return bool
     */
    protected function _isCoreCheckoutUrlHelperOverridden()
    {
        return get_class(Mage::helper('checkout/url')) != 'Mage_Checkout_Helper_Url';
    }
}
