<?php

class Rejoiner_Acr_Model_Subscribe_JsConfig extends Mage_Core_Model_Abstract
{
    protected $_options = array (
        Rejoiner_Acr_Helper_Data::PAGE_CUSTOMER_REGISTER => 'customerRegister',
        Rejoiner_Acr_Helper_Data::PAGE_CUSTOMER_LOGIN => 'customerLogin',
        Rejoiner_Acr_Helper_Data::PAGE_NEWSLETTER_MANAGE => 'customerAccount',
        Rejoiner_Acr_Helper_Data::PAGE_CHECKOUT_ONEPAGE => 'checkoutOnePage',
    );
    public function __construct()
    {
        $this->_init('rejoiner_acr/subscribe_jsConfig');
    }

    public function getJsonConfig($page)
    {
        $configJson = array (
            "subscribePage" => $this->_options[$page],
            "customerRegister" => (int) Mage::getStoreConfig(Rejoiner_Acr_Helper_Data::XML_PATH_REJOINER_SUBSCRIBE_ACCOUNT_REGISTRATION),
            "customerLogin" => (int) Mage::getStoreConfig(Rejoiner_Acr_Helper_Data::XML_PATH_REJOINER_SUBSCRIBE_LOGIN_CHECKOUT),
            "customerAccount" => (int) Mage::getStoreConfig(Rejoiner_Acr_Helper_Data::XML_PATH_REJOINER_SUBSCRIBE_CUSTOMER_ACCOUNT),
            "guestCheckout" => (int) Mage::getStoreConfig(Rejoiner_Acr_Helper_Data::XML_PATH_REJOINER_SUBSCRIBE_GUEST_CHECKOUT),
            "isLoggedIn" => Mage::helper('customer')->isLoggedIn(),
            "isAlreadySubscribed" => Mage::helper('rejoiner_acr')->getIsSubscribed(),
            "customerRegisterForm" => 'form-validate',
            "customerLoginForm" => 'login-form',
            "customerAccountForm" => 'form-validate',
            "guestCheckoutForm" => 'co-billing-form',
            "checkoutOnePageForm" => 'co-billing-form',
            "customerRegisterBlock" => 'is_subscribed',
            "customerAccountBlock" => 'subscription',
            "checkoutAnchor" => 'billing:email',
            "checkboxDefault" => (int) Mage::getStoreConfig(Rejoiner_Acr_Helper_Data::XML_PATH_REJOINER_SUBSCRIBE_CHECKBOX_DEFAULT),
            "checkboxLabel" => Mage::getStoreConfig(Rejoiner_Acr_Helper_Data::XML_PATH_REJOINER_SUBSCRIBE_CHECKBOX_LABEL),
            "checkboxSelector" => trim(Mage::getStoreConfig(Rejoiner_Acr_Helper_Data::XML_PATH_REJOINER_SUBSCRIBE_CHECKBOX_SELECTOR), '.'),
            "checkboxStyle" => Mage::getStoreConfig(Rejoiner_Acr_Helper_Data::XML_PATH_REJOINER_SUBSCRIBE_CHECKBOX_STYLE),
            "rejoinerId" => 'rejoiner_subscribe'
        );
        $configJson = new Varien_Object($configJson);
        Mage::dispatchEvent('rejoiner_subscribe_js_config', array('config_json' => $configJson));
        $configJson = $configJson->getData();
        return Mage::helper('core')->jsonEncode($configJson);
    }

}
