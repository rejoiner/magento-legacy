<?php

/**
 * Main block for module
 *
 * @category   Rejoiner
 * @package    Rejoiner_Acr
 */
class Rejoiner_Acr_Block_Snippets extends Rejoiner_Acr_Block_Base
{

    const XML_PATH_REJOINER_TRACK_PRICE_WITH_TAX = 'checkout/rejoiner_acr/price_with_tax';

    /**
     * @return array
     */
    public function getCartData()
    {
        $cartData = array();
        if ($quote = $this->_getQuote()) {
            if ($this->getTrackPriceWithTax()) {
                $total = $this->_getQuote()->getGrandTotal();
            } else {
                $total = $this->_getQuote()->getSubtotal();
            }

            $rejoinerHelper = Mage::helper('rejoiner_acr');

            $cartData = array(
                'cart_item_count' => (int) $this->_getQuote()->getItemsQty(),
                'cart_value'        => $this->_convertPriceToCents($total),
                'return_url'        => (string) $rejoinerHelper->getRestoreUrl(),
            );

            if ($rejoinerHelper->getCartCouponsEnabled()) {
                $couponCodeParam = $rejoinerHelper->getCouponParam('cart');
                $cartData[$couponCodeParam] = (string) $this->_generateCouponCode('cart');
            }
        }
        return $cartData;
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    /**
     * @param $price
     * @return float
     */
    protected function _convertPriceToCents($price) {
        return round($price*100);
    }

    /**
     * @return bool
     */
    protected function getTrackPriceWithTax()
    {
        return Mage::getStoreConfig(self::XML_PATH_REJOINER_TRACK_PRICE_WITH_TAX);
    }
}
