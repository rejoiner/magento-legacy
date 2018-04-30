<?php
class Rejoiner_Acr_Model_Observer
{
    const REJOINER_API_URL = 'https://app.rejoiner.com';
    const REJOINER_API_REQUEST_PATH = '/api/1.0/site/%s/lead/convert';
    const REJOINER_API_LOG_FILE = 'rejoiner_api.log';

    const XML_PATH_REJOINER_API_KEY     = 'checkout/rejoiner_acr/api_key';
    const XML_PATH_REJOINER_API_SECRET  = 'checkout/rejoiner_acr/api_secret';
    const XML_PATH_REJOINER_API_SITE_ID = 'checkout/rejoiner_acr/site_id';

    public function trackOrderSuccessConversion(Varien_Event_Observer $observer)
    {
        $apiKey = Mage::getStoreConfig(self::XML_PATH_REJOINER_API_KEY);
        $apiSecret = utf8_encode(Mage::getStoreConfig(self::XML_PATH_REJOINER_API_SECRET));

        if ($apiKey && $apiSecret) {
            /** @var Mage_Checkout_Model_Session $session */
            $lastOrderId = $observer->getEvent()->getData('order_ids');
            /** @var Mage_Sales_Model_Order $order */
            $order = Mage::getModel('sales/order')->load($lastOrderId[0]);
            $customerEmail = $order->getBillingAddress()->getEmail();

            $siteId = Mage::getStoreConfig(self::XML_PATH_REJOINER_API_SITE_ID);
            $requestPath = sprintf(self::REJOINER_API_REQUEST_PATH, $siteId);
            $requestBody = utf8_encode(sprintf('{"email": "%s"}', $customerEmail));
            $hmacData = utf8_encode(implode("\n", array(Varien_Http_Client::POST, $requestPath, $requestBody)));

            $codedApiSecret = base64_encode(hash_hmac('sha1', $hmacData, $apiSecret, true));
            $authorization = sprintf('Rejoiner %s:%s', $apiKey , $codedApiSecret);
            $client = new Varien_Http_Client(self::REJOINER_API_URL . $requestPath);
            $client->setRawData($requestBody);
            $client->setHeaders(array('Authorization' => $authorization, 'Content-type' => 'application/json;' ));
            try{
                $response = $client->request(Varien_Http_Client::POST);
                    switch ($response->getStatus()) {
                        case '200':
                            Mage::log(print_r($response->getStatus(), true) . ' Everything is alright.', null, self::REJOINER_API_LOG_FILE);
                            break;
                        case '400':
                            Mage::log(print_r($response->getStatus(), true) . ' required params were not specified and/or the body was malformed', null, self::REJOINER_API_LOG_FILE);
                            break;
                        case '403':
                            Mage::log(print_r($response->getStatus(), true) . ' failed authentication and/or incorrect signature', null, self::REJOINER_API_LOG_FILE);
                            break;
                        case '500':
                            Mage::log(print_r($response->getStatus(), true) . ' internal error, contact us for details', null, self::REJOINER_API_LOG_FILE);
                            break;
                        default:
                            Mage::log(print_r($response->getStatus(), true) . ' unexpected response code', null, self::REJOINER_API_LOG_FILE);
                            break;
                    }
            } catch (Exception $e) {
                Mage::log($e->getMessage(), null, 'exception.log');
            }
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function removeCartItem(Varien_Event_Observer $observer)
    {
        $session = Mage::getSingleton('core/session',  array('name' => 'frontend'));
        /** @var Mage_Sales_Model_Quote_Item $quote */
        if ($quote = $observer->getQuoteItem()) {
            $removedItem[] = $quote->getSku();
            $session->setData(Rejoiner_Acr_Helper_Data::REMOVED_CART_ITEM_SKU_VARIABLE, $removedItem);
        }
    }
}