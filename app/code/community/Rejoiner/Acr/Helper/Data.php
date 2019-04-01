<?php

/**
 * Generic helper for module
 *
 * @category   Rejoiner
 * @package    Rejoiner_Acr
 */
class Rejoiner_Acr_Helper_Data extends Mage_Core_Helper_Abstract
{

    const XML_PATH_REJOINER_ENABLED         = 'checkout/rejoiner_acr/enabled';
    const XML_PATH_REJOINER_SITE_ID         = 'checkout/rejoiner_acr/site_id';
    const XML_PATH_REJOINER_DOMAIN          = 'checkout/rejoiner_acr/domain';
    const XML_PATH_REJOINER_TRACK_NUMBERS   = 'checkout/rejoiner_acr/track_numbers';
    const XML_PATH_REJOINER_PERSIST_FORMS   = 'checkout/rejoiner_acr/persist_forms';
    const XML_PATH_REJOINER_THUMBNAIL_SIZE  = 'checkout/rejoiner_acr/thumbnail_size';
    const XML_PATH_REJOINER_DEBUG_ENABLED   = 'checkout/rejoiner_acr/debug_enabled';
    const REMOVED_CART_ITEM_SKU_VARIABLE    = 'rejoiner_sku';
    const XML_PATH_REJOINER_MARKETING_PERMISSIONS = 'checkout/rejoiner_acr/marketing_permissions';
    const XML_PATH_REJOINER_MARKETING_LIST_ID= 'checkout/rejoiner_acr/marketing_list_id';
    const XML_PATH_REJOINER_SUBSCRIBE_GUEST_CHECKOUT = 'checkout/rejoiner_acr/subscribe_checkout_onepage_index';
    const XML_PATH_REJOINER_SUBSCRIBE_ACCOUNT_REGISTRATION = 'checkout/rejoiner_acr/subscribe_customer_account_create';
    const XML_PATH_REJOINER_SUBSCRIBE_LOGIN_CHECKOUT = 'checkout/rejoiner_acr/subscribe_customer_account_login';
    const XML_PATH_REJOINER_SUBSCRIBE_CUSTOMER_ACCOUNT = 'checkout/rejoiner_acr/subscribe_newsletter_manage_index';
    const XML_PATH_REJOINER_SUBSCRIBE_CHECKBOX_DEFAULT = 'checkout/rejoiner_acr/subscribe_checkbox_default';
    const XML_PATH_REJOINER_SUBSCRIBE_CHECKBOX_LABEL = 'checkout/rejoiner_acr/subscribe_checkbox_label';
    const XML_PATH_REJOINER_SUBSCRIBE_CHECKBOX_SELECTOR = 'checkout/rejoiner_acr/subscribe_checkbox_selector';
    const XML_PATH_REJOINER_SUBSCRIBE_CHECKBOX_STYLE = 'checkout/rejoiner_acr/subscribe_checkbox_style';
    const XML_PATH_REJOINER_CART_COUPON_ENABLED = 'checkout/rejoiner_acr/coupon_code';
    const XML_PATH_REJOINER_CART_COUPON_SALESRULE = 'checkout/rejoiner_acr/salesrule_model';
    const XML_PATH_REJOINER_CART_COUPON_PARAM = 'checkout/rejoiner_acr/coupon_code_param';
    const XML_PATH_REJOINER_BROWSE_COUPON_ENABLED = 'checkout/rejoiner_acr/coupon_code_browse';
    const XML_PATH_REJOINER_BROWSE_COUPON_SALESRULE = 'checkout/rejoiner_acr/salesrule_model_browse';
    const XML_PATH_REJOINER_BROWSE_COUPON_PARAM = 'checkout/rejoiner_acr/coupon_code_param_browse';

    const PAGE_CUSTOMER_REGISTER  = 'customer_account_create';
    const PAGE_CUSTOMER_LOGIN  = 'customer_account_login';
    const PAGE_NEWSLETTER_MANAGE  = 'newsletter_manage_index';
    const PAGE_CHECKOUT_ONEPAGE  = 'checkout_onepage_index';

    const DEFAULT_COUPON_PARAM = 'promo';

    const REJOINER_VERSION_1 = 'v1';
    const REJOINER_VERSION_2 = 'v2';

    const REJOINER2_SITE_ID_LENGTH = 7;


    protected $_currentProtocolSecurity = null;

    /**
     * @return string
     */
    public function getRejoinerSiteId()
    {
        return Mage::getStoreConfig(self::XML_PATH_REJOINER_SITE_ID);
    }

    /**
     * @return string
     */
    public function getRejoinerVersion()
    {
        $siteIdLength = strlen($this->getRejoinerSiteId());

        if ($siteIdLength == self::REJOINER2_SITE_ID_LENGTH) {
            return self::REJOINER_VERSION_2;
        }

        return self::REJOINER_VERSION_1;
    }

    /**
     * @return string
     */
    public function getRejoinerScriptUri()
    {
        switch ($this->getRejoinerVersion()) {
            case self::REJOINER_VERSION_2:
                return 'https://cdn.rejoiner.com/js/v4/rj2.lib.js';
            default:
                return 'https://cdn.rejoiner.com/js/v4/rejoiner.lib.js';
        }
    }

    public function getRestoreUrl()
    {
        $product = array();
        /** @var Mage_Checkout_Helper_Cart $cartHelper */
        $cartHelper = Mage::helper('checkout/cart');

        if ($itemsCollection = $cartHelper->getCart()->getItems()) {
            /** @var Mage_Sales_Model_Quote_Item $item */
            foreach ($itemsCollection as $item) {
                if (!$item->getParentItem()) {
                    $options = unserialize($item->getOptionByCode('info_buyRequest')->getValue());
                    $options['qty'] = $item->getQty();
                    $options['product'] = $item->getProductId();
                    $product[] = $options;
                }
            }
        }

        $googleAttributesArray = $this->returnGoogleAttributes();
        $customAttributesArray = $this->returnCustomAttributes();

        $queryParams = array_merge($product, $googleAttributesArray, $customAttributesArray);

        $url = Mage::getUrl('rejoiner/addtocart/', array(
            '_query' => $queryParams,
            '_secure' => true
        ));

        $lastCharOfUrl = substr($url, strlen($url) - 1);

        switch ($lastCharOfUrl) {
            case '/':
                return substr($url, 0, strlen($url) - 1);
            default:
                return $url;
        }
    }

    public function getDomain()
    {
        $domain = trim(Mage::getStoreConfig(self::XML_PATH_REJOINER_DOMAIN));
        if ($domain[0] == '.') {
            return $domain;
        } else {
            return '.' . $domain;
        }
    }

    /**
     * @return bool
     */
    public function getTrackNumberEnabled()
    {
        return Mage::getStoreConfig(self::XML_PATH_REJOINER_TRACK_NUMBERS);
    }

    /**
     * @return bool
     */
    public function getPersistFormsEnabled()
    {
        return Mage::getStoreConfig(self::XML_PATH_REJOINER_PERSIST_FORMS);
    }

    /**
     * @return bool
     */
    public function getCartCouponsEnabled()
    {
        return Mage::getStoreConfig(self::XML_PATH_REJOINER_CART_COUPON_ENABLED);
    }

    /**
     * @return bool
     */
    public function getBrowseCouponsEnabled()
    {
        return Mage::getStoreConfig(self::XML_PATH_REJOINER_BROWSE_COUPON_ENABLED);
    }

    /**
     * @param string $couponType
     * @return string
     */
    public function getCouponRuleId($couponType)
    {
        switch ($couponType) {
            case 'cart':
                return Mage::getStoreConfig(self::XML_PATH_REJOINER_CART_COUPON_SALESRULE);
            case 'browse':
                return Mage::getStoreConfig(self::XML_PATH_REJOINER_BROWSE_COUPON_SALESRULE);
        }
    }

    /**
     * @param string $couponType
     * @return string
     */
    public function getCouponParam($couponType)
    {
        switch ($couponType) {
            case 'cart':
                $couponParam = Mage::getStoreConfig(self::XML_PATH_REJOINER_CART_COUPON_PARAM);
                break;
            case 'browse':
                $couponParam = Mage::getStoreConfig(self::XML_PATH_REJOINER_BROWSE_COUPON_PARAM);
                break;
            default:
                $couponParam = '';
        }

        if (strlen($couponParam)) {
            return $couponParam;
        }

        return self::DEFAULT_COUPON_PARAM;
        
    }

    /**
     * @return bool
     */
    public function checkHttps()
    {
        if (empty($this->_currentProtocolSecurity)) {
            $this->_currentProtocolSecurity = Mage::app()->getStore()->isCurrentlySecure();
        }
        return $this->_currentProtocolSecurity;
    }

    /**
     * @param $productImageName
     * @return bool
     */

    public function resizeImage($productImageName)
    {
        if($size = $this->_parseSize(Mage::getStoreConfig(self::XML_PATH_REJOINER_THUMBNAIL_SIZE))) {
            $imageResized = Mage::getBaseDir('media') . '/catalog/resized/' . $size['width'] . 'x' . $size['height'] . $productImageName;
            if (!file_exists($imageResized)) {
                $imageObj = new Varien_Image(Mage::getBaseDir('media') . '/catalog/product' . $productImageName);
                $imageObj->resize($size['width'], $size['height']);
                $imageObj->save($imageResized);
            }
        } else {
            return false;
        }
        return $imageResized;
    }

    /**
     * @param $string
     * @return array|bool
     */

    protected function _parseSize($string)
    {
        $size = explode('x', strtolower($string));
        if (sizeof($size) == 2) {
            return array(
                'width' => ($size[0] > 0) ? $size[0] : null,
                'height' => ($size[1] > 0) ? $size[1] : null,
            );
        }
        return false;
    }

    /**
     * @return bool
     */

    /**
     * @return bool|mixed
     */
    public function checkRemovedItem()
    {
        $session = Mage::getSingleton('core/session',  array('name' => 'frontend'));
        if ($session->hasData(self::REMOVED_CART_ITEM_SKU_VARIABLE)) {
            $removedItems = $session->getData(self::REMOVED_CART_ITEM_SKU_VARIABLE);
            $session->unsetData(self::REMOVED_CART_ITEM_SKU_VARIABLE);
            return $removedItems;
        }
        return false;
    }

    /**
     * @return array
     */
    public function returnGoogleAttributes() {
        $result=array();
        if ($googleAnalitics = Mage::getStoreConfig('checkout/rejoiner_acr/google_attributes')) {
            foreach (unserialize($googleAnalitics) as $attr) {
                if ($attr['attr_name'] && $attr['value']) {
                    $result[$attr['attr_name']] = $attr['value'];
                }
            }
        }
        return $result;
    }

    /**
     * @return array
     */
    public function returnCustomAttributes() {
        $result=array();
        if ($customAttr = Mage::getStoreConfig('checkout/rejoiner_acr/custom_attributes')) {
            foreach (unserialize($customAttr) as $attr) {
                if ($attr['attr_name'] && $attr['value']) {
                    $result[$attr['attr_name']] = $attr['value'];
                }
            }
        }
        return $result;
    }


    /**
     * @param Mage_Catalog_Model_Product $product
     * @param $categoriesArray
     * @return array
     */

    public function getProductCategories(Mage_Catalog_Model_Product $product, $categoriesArray)
    {
        $result = array();
        foreach ($product->getCategoryIds() as $catId) {
            if (isset($categoriesArray[$catId])) {
                $result[] = $categoriesArray[$catId]->getName();
            }
        }
        return $result;
    }

    /**
     * Get the Catalog Inventory Qty value for a specific product.
     *
     * @param Mage_Catalog_Model_Product $product Product itself
     *
     * @return int
     */
    public function getProductStockLevel(Mage_Catalog_Model_Product $product)
    {
        /**
         * Create a Stock Item instance of the specific product.
         *
         * @var Mage_CatalogInventory_Model_Stock_Item $stockItem
         */
        $stockItem = Mage::getModel('cataloginventory/stock_item')
            ->loadByProduct($product);

        return (int) $stockItem->getQty();
    }

    /**
     * @return bool
     */
    public function getIsSubscribed()
    {
        if (Mage::getSingleton('customer/session')->getIsAlreadySubscribed()) {
            return true;
        }
        $email = Mage::getSingleton('customer/session')->getCustomer()->getEmail();
        $subscriber = Mage::getModel('newsletter/subscriber')->load($email, 'subscriber_email');
        if ($subscriber->getId() && $subscriber->isSubscribed()) {
            Mage::getSingleton('customer/session')->setIsAlreadySubscribed(true);
            return true;
        }
        return false;
    }
}
