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

    protected $_currentProtocolSecurity = null;

    public function getRejoinerSiteId()
    {
        return Mage::getStoreConfig(self::XML_PATH_REJOINER_SITE_ID);
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
        return substr($url, 0, strlen($url)-1);
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
        return implode(' ', $result);
    }
}
