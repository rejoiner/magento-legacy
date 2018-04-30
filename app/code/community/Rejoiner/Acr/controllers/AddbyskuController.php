<?php

class Rejoiner_Acr_AddbyskuController extends Mage_Core_Controller_Front_Action
{
    const XML_PATH_REJOINER_DEBUG_ENABLED   = 'checkout/rejoiner_acr/debug_enabled';

    public function indexAction()
    {
        $params = $this->getRequest()->getParams();
        /** @var Mage_Checkout_Helper_Cart $cart */
        $cart = Mage::helper('checkout/cart');
        $quote = $cart->getQuote();

        $successMessage = '';
        foreach ($params as $key => $product) {
            if ($product && is_array($product)) {
                if (!isset($product['sku'])) {
                    continue;
                }
                /** @var Mage_Catalog_Model_Product $productModel */
                $productModel = Mage::getModel('catalog/product');
                /** @var Mage_Catalog_Model_Product $productBySKU */
                $productBySKU = $productModel->loadByAttribute('sku', $product['sku']);
                if (!$productBySKU->getId()) {
                    continue;
                }
                $productId = $productBySKU->getId();
                if ($productId) {
                    /** @var Mage_CatalogInventory_Model_Stock_Item $stockItem */
                    $stockItem = Mage::getModel('cataloginventory/stock_item');
                    $qty = $stockItem->loadByProduct($productId)->getQty();
                    try {
                        if(!$quote->hasProductId($productId) && is_numeric($product['qty']) && $qty > $product['qty']) {
                            $quote->addProduct($productBySKU, (int)$product['qty']);
                            $successMessage .= $this->__('%s was added to your shopping cart.'.'</br>', Mage::helper('core')->escapeHtml($productBySKU->getName()));
                        }
                        unset($params[$key]);
                    } catch (Exception $e) {
                        if(Mage::getStoreConfig(Rejoiner_Acr_Helper_Data::XML_PATH_REJOINER_DEBUG_ENABLED)) {
                            Mage::log($e->getMessage(), null, 'rejoiner.log');
                        }
                    }
                }
            }
        }
        if ($params['coupon_code']) {
            $quote->setCouponCode($params['coupon_code'])->collectTotals()->save();;
        }
        try {
            $quote->save();
        }  catch (Exception $e) {
            if(Mage::getStoreConfig(self::XML_PATH_REJOINER_DEBUG_ENABLED)) {
                Mage::log($e->getMessage(), null, 'rejoiner.log');
            }
        }
        Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
        if($successMessage) {
            Mage::getSingleton('core/session')->addSuccess($successMessage);
        }
        $this->getResponse()->setRedirect(Mage::getUrl('checkout/cart/'));
    }
}