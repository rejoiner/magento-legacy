<?php

class Rejoiner_Acr_AddtocartController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        /** @var Mage_Checkout_Model_Cart $cart */
        $cart = Mage::getSingleton('checkout/cart');
        $cart->truncate();
        $params = $this->getRequest()->getParams();
        foreach ($params as $key => $product) {
            if ($product && is_array($product)) {
                $prodModel = Mage::getModel('catalog/product');
                $prodModel->load((int)$product['product']);
                if (!$prodModel->getId()) {
                    continue;
                }
                try {
                    $cart->addProduct($prodModel, $product);
                    unset($params[$key]);
                } catch (Exception $e) {
                    if(Mage::getStoreConfig(Rejoiner_Acr_Helper_Data::XML_PATH_REJOINER_DEBUG_ENABLED)) {
                        Mage::log($e->getMessage(), null, 'rejoiner.log');
                    }
                }
            }
        }
        if ($params['coupon_code']) {
            $cart->getQuote()->setCouponCode($params['coupon_code'])->collectTotals()->save();;
        }
        $cart->save();
        Mage::getSingleton('checkout/session')->setCartWasUpdated(true);

        /** @var Rejoiner_Acr_Helper_Data $rejoinerHelper */
        $rejoinerHelper = Mage::helper('rejoiner_acr');
        $queryParams = array_merge($rejoinerHelper->returnGoogleAttributes(), $params);
        $this->getResponse()->setRedirect(Mage::getUrl('checkout/cart/', array('_query' => $queryParams)));
    }
}