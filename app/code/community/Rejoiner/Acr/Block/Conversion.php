<?php
class Rejoiner_Acr_Block_Conversion extends Rejoiner_Acr_Block_Base
{
    const XML_PATH_REJOINER_TRACK_PRICE_WITH_TAX = 'checkout/rejoiner_acr/price_with_tax';

    /**
     * @return array
     */
    public function getCartData()
    {
        $cartData = array();
        if ($quote = $this->_getQuote()) {
            /** @var Mage_Checkout_Model_Session $session */
            $session = Mage::getSingleton('checkout/session');
            if ($this->getTrackPriceWithTax()) {
                $total = $quote->getGrandTotal();
            } else {
                $total = $quote->getSubtotal();
            }
            $cartData = array(
                'cart_value' => $this->_convertPriceToCents($total),
                'cart_item_count' => intval($quote->getItemsQty()),
                'customer_order_number' => $session->getLastRealOrderId(),
                'return_url' => Mage::getUrl('sales/order/view/', array('order_id' => $session->getLastOrderId()))
            );

            if ($promo = $quote->getCouponCode()) {
                $cartData['promo'] = $promo;
            }

        }
        return $cartData;
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote()
    {
        $quote = Mage::getModel('sales/quote');
        if ($quoteId = Mage::getSingleton('checkout/session')->getLastQuoteId()) {
            $quote->load($quoteId);
        }
        return $quote;
    }

}