<?php
class Rejoiner_Acr_Model_Observer
{
    const REJOINER_API_LOG_FILE = 'rejoiner_api.log';

    const XML_PATH_REJOINER_API_KEY      = 'checkout/rejoiner_acr/api_key';
    const XML_PATH_REJOINER_API_SECRET   = 'checkout/rejoiner_acr/api_secret';
    const XML_PATH_REJOINER_API_SITE_ID  = 'checkout/rejoiner_acr/site_id';
    const XML_PATH_REJOINER_API_DEBUGGER        = 'checkout/rejoiner_acr/debug_enabled';
    const XML_PATH_REJOINER_PASS_NEW_CUSTOMERS = 'checkout/rejoiner_acr/passing_new_customers';
    const XML_PATH_REJOINER_LIST_ID             = 'checkout/rejoiner_acr/list_id';

    /**
     * @return string
     */
    public function getRejoinerSiteId()
    {
        return Mage::getStoreConfig(self::XML_PATH_REJOINER_API_SITE_ID);
    }

    /**
     * @return string
     */
    public function getRejoinerVersion()
    {
        $siteId = $this->getRejoinerSiteId();
        $siteIdLength = strlen($siteId);

        if ($siteIdLength == Rejoiner_Acr_Helper_Data::REJOINER2_SITE_ID_LENGTH) {
            return Rejoiner_Acr_Helper_Data::REJOINER_VERSION_2;
        }

        return Rejoiner_Acr_Helper_Data::REJOINER_VERSION_1;
    }

    /**
     * @return string
     */
    public function getRejoinerApiKey()
    {
        return Mage::getStoreConfig(self::XML_PATH_REJOINER_API_KEY);
    }

    /**
     * @return string|boolean
     */
    public function getRejoinerApiSecret()
    {
        switch ($this->getRejoinerVersion()) {
            case Rejoiner_Acr_Helper_Data::REJOINER_VERSION_2:
                return true;
            default:
                return Mage::getStoreConfig(self::XML_PATH_REJOINER_API_SECRET);
        }
    }

    /**
     * @return string
     */
    public function getRejoinerApiUri()
    {
        switch ($this->getRejoinerVersion()) {
            case Rejoiner_Acr_Helper_Data::REJOINER_VERSION_2:
                return 'https://rj2.rejoiner.com';
            default:
                return 'https://app.rejoiner.com';
        }
    }

    /**
     * @return string
     */
    public function getRejoinerApiPath()
    {
        switch ($this->getRejoinerVersion()) {
            case Rejoiner_Acr_Helper_Data::REJOINER_VERSION_2:
                return '/api/v1/%s';
            default:
                return '/api/1.0/site/%s';
        }
    }

    /**
     * @return string
     */
    public function getRejoinerApiConvertPath()
    {
        switch ($this->getRejoinerVersion()) {
            case Rejoiner_Acr_Helper_Data::REJOINER_VERSION_2:
                return $this->getRejoinerApiPath() . '/customer/convert/';
            default:
                return $this->getRejoinerApiPath() . '/lead/convert';
        }
    }

    /**
     * @return string
     */
    public function getRejoinerApiAddToListPath($listId)
    {
        switch ($this->getRejoinerVersion()) {
            case Rejoiner_Acr_Helper_Data::REJOINER_VERSION_2:
                return $this->getRejoinerApiPath() . "/lists/$listId/contacts/";
            default:
                return $this->getRejoinerApiPath() . '/contact_add';
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function trackOrderSuccess(Varien_Event_Observer $observer)
    {
        $apiKey = $this->getRejoinerApiKey();
        $apiSecret = utf8_encode($this->getRejoinerApiSecret());

        if ($apiKey && $apiSecret) {
            /** @var Mage_Checkout_Model_Session $session */
            $lastOrderId = $observer->getEvent()->getData('order_ids');
            /** @var Mage_Sales_Model_Order $order */
            $order = Mage::getModel('sales/order')->load($lastOrderId[0]);
            if (!$order->getId()) {
                return $this;
            }
            $customerEmail = $order->getCustomerEmail();
            $siteId = Mage::getStoreConfig(self::XML_PATH_REJOINER_API_SITE_ID);
            $this->convert($apiKey, $apiSecret, $siteId, $customerEmail);
            $listId = Mage::getStoreConfig(self::XML_PATH_REJOINER_LIST_ID);
            $data = array(
                'email' => $order->getCustomerEmail(),
                'list_id' => $listId,
                'first_name' => $order->getCustomerFirstname()
            );
            if (Mage::getStoreConfig(Rejoiner_Acr_Helper_Data::XML_PATH_REJOINER_SUBSCRIBE_GUEST_CHECKOUT)) {
                if (Mage::getSingleton('core/session')->getIsSubscribed()) {
                    if (Mage::getStoreConfig(self::XML_PATH_REJOINER_PASS_NEW_CUSTOMERS) && $listId) {
                        $this->addToList($apiKey, $apiSecret, $siteId, $data);
                    }
                    if (Mage::getStoreConfig(Rejoiner_Acr_Helper_Data::XML_PATH_REJOINER_MARKETING_PERMISSIONS)) {
                        $this->updateSubscribe(true, $customerEmail, $order->getCustomerFirstname());
                    }
                    Mage::getSingleton('core/session')->unsIsSubscribed();
                    Mage::getSingleton('customer/session')->unsIsAlreadySubscribed();
                }
            } else {
                if (Mage::getStoreConfig(self::XML_PATH_REJOINER_PASS_NEW_CUSTOMERS) && $listId) {
                    $this->addToList($apiKey, $apiSecret, $siteId, $data);
                }
            }
        }
        return $this;
    }

    /**
     * @param $requestBody
     * @param $requestPath
     * @param $apiSecret
     * @return string
     */
    private function buildAuth($requestBody, $requestPath, $apiKey, $apiSecret)
    {
        $authorization  = sprintf('Rejoiner %s', $apiKey);

        if ($this->getRejoinerVersion() == Rejoiner_Acr_Helper_Data::REJOINER_VERSION_1) {
            $hmacData       = utf8_encode(implode("\n", array(Varien_Http_Client::POST, $requestPath, $requestBody)));
            $codedApiSecret = base64_encode(hash_hmac('sha1', $hmacData, $apiSecret, true));
            $authorization  = sprintf('Rejoiner %s:%s', $apiKey, $codedApiSecret);
        }

        return $authorization;
    }

    /**
     * @param $apiKey
     * @param $apiSecret
     * @param $siteId
     * @param $customerEmail
     * @return $this
     */
    private function convert($apiKey, $apiSecret, $siteId, $customerEmail)
    {
        $requestBody    = utf8_encode(sprintf('{"email": "%s"}', $customerEmail));
        $requestPath    = sprintf($this->getRejoinerApiConvertPath(), $siteId);
        $authorization  = $this->buildAuth($requestBody, $requestPath, $apiKey, $apiSecret);
        $client         = new Varien_Http_Client($this->getRejoinerApiUri() . $requestPath);
        $client->setRawData($requestBody);
        $client->setHeaders(array('Authorization' => $authorization, 'Content-type' => 'application/json;' ));
        $this->sendRequest($client);
        return $this;
    }

    /**
     * @param $apiKey
     * @param $apiSecret
     * @param $siteId
     * @param $data
     * @return $this
     */
    private function addToList($apiKey, $apiSecret, $siteId, $data)
    {
        $listId         = $data['list_id'];
        $requestBody    = utf8_encode(json_encode($data));
        $requestPath    = sprintf($this->getRejoinerApiAddToListPath($listId), $siteId);
        $authorization  = $this->buildAuth($requestBody, $requestPath, $apiKey, $apiSecret);
        $client         = new Varien_Http_Client($this->getRejoinerApiUri() . $requestPath);
        $client->setRawData($requestBody);
        $client->setHeaders(array('Authorization' => $authorization, 'Content-type' => 'application/json;' ));
        $this->sendRequest($client);
        return $this;
    }

    private function sendRequest($client)
    {
        try{
            $response = $client->request(Varien_Http_Client::POST);
            switch ($response->getStatus() && Mage::getStoreConfig(self::XML_PATH_REJOINER_API_DEBUGGER)) {
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
        return $this;
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

    /**
     * @param $isSubscribed
     * @param $customerEmail
     * @param $customerName
     * @return $this
     */
    public function updateSubscribe($isSubscribed,  $customerEmail, $customerName)
    {
        $apiKey = $this->getRejoinerApiKey();
        $apiSecret = utf8_encode($this->getRejoinerApiSecret());
        $listId = Mage::getStoreConfig(Rejoiner_Acr_Helper_Data::XML_PATH_REJOINER_MARKETING_LIST_ID);
        if ($apiKey && $apiSecret && $listId) {
            $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($customerEmail);
            $siteId = Mage::getStoreConfig(self::XML_PATH_REJOINER_API_SITE_ID);
            if ($isSubscribed) {
                if (!$subscriber->getId()) {
                    $subscriber->subscribe($customerEmail);
                }
                $data = array(
                    'email' => $customerEmail,
                    'list_id' => $listId,
                    'first_name' => $customerName
                );
                $this->addToList($apiKey, $apiSecret, $siteId, $data);
                try {
                    $subscriber->setAddedToRejoiner(1)
                        ->save();
                } catch (Exception $e) {
                    Mage::logException($e);
                }
                if (!$subscriber->isSubscribed()) {
                    $subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED)
                        ->save();
                }
            }
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function customerSubscribeUpdate(Varien_Event_Observer $observer)
    {
        if (Mage::getStoreConfig(Rejoiner_Acr_Helper_Data::XML_PATH_REJOINER_MARKETING_PERMISSIONS)) {
            $customer = $observer->getEvent()->getData('customer');
            if (Mage::app()->getRequest()->getControllerModule() == 'Mage_Newsletter' && !Mage::getStoreConfig(Rejoiner_Acr_Helper_Data::XML_PATH_REJOINER_SUBSCRIBE_CUSTOMER_ACCOUNT)) {
                return $this;
            }
            $this->updateSubscribe($customer->getIsSubscribed(), $customer->getEmail(), $customer->getFirstname());
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function customerLogin(Varien_Event_Observer $observer)
    {
        if (Mage::getStoreConfig(Rejoiner_Acr_Helper_Data::XML_PATH_REJOINER_MARKETING_PERMISSIONS)) {
            /** @var $controllerAction Mage_Core_Controller_Varien_Action */
            $controllerAction = $observer->getEvent()->getControllerAction();
            if ($controllerAction) {
                $isSubscribed = $controllerAction->getRequest()->getPost('is_subscribed');
                $customerEmail = $controllerAction->getRequest()->getPost('login')['username'];
                $customer = Mage::getModel('customer/customer');
                $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
                $customer->loadByEmail($customerEmail);
                if (Mage::getStoreConfig(Rejoiner_Acr_Helper_Data::XML_PATH_REJOINER_SUBSCRIBE_LOGIN_CHECKOUT)) {
                    $this->updateSubscribe($isSubscribed, $customerEmail, $customer->getFirstname());
                }
            }
            return $this;
        }
    }

    public function customerCheckout(Varien_Event_Observer $observer)
    {
        if (Mage::getStoreConfig(Rejoiner_Acr_Helper_Data::XML_PATH_REJOINER_MARKETING_PERMISSIONS)) {

            /** @var $controllerAction Mage_Core_Controller_Varien_Action */
            $controllerAction = $observer->getEvent()->getControllerAction();
            if ($controllerAction) {
                $data = $controllerAction->getRequest()->getPost();
                $isSubscribed = $data['is_subscribed'];
                if ($isSubscribed || Mage::helper('rejoiner_acr')->getIsSubscribed())
                Mage::getSingleton('core/session')->setIsSubscribed(true);
                $email = (Mage::helper('customer')->isLoggedIn()) ? Mage::getSingleton('customer/session')->getCustomer()->getEmail() : $data['billing']['email'];
                $this->updateSubscribe($isSubscribed, $email, $data['billing']['firstname']);
            }
            return $this;
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function handleDefaultSubscribe(Varien_Event_Observer $observer)
    {
        if (Mage::getStoreConfig(Rejoiner_Acr_Helper_Data::XML_PATH_REJOINER_MARKETING_PERMISSIONS)) {
            $subscriber = $observer->getSubscriber();
            if ($subscriber->getId() && (Mage::app()->getRequest()->getControllerModule() == 'Mage_Newsletter' && Mage::app()->getRequest()->getControllerName() != 'subscriber')) {
                $subscriber->setSubscriberStatus(1);
            }
            if (!Mage::getStoreConfig(Rejoiner_Acr_Helper_Data::XML_PATH_REJOINER_SUBSCRIBE_CUSTOMER_ACCOUNT) && (Mage::app()->getRequest()->getControllerModule() == 'Mage_Newsletter' && Mage::app()->getRequest()->getControllerName() != 'subscriber')) {
                return $this;
            }
            $apiKey = $this->getRejoinerApiKey();
            $apiSecret = utf8_encode($this->getRejoinerApiSecret());
            $siteId = Mage::getStoreConfig(self::XML_PATH_REJOINER_API_SITE_ID);
            $listId = Mage::getStoreConfig(Rejoiner_Acr_Helper_Data::XML_PATH_REJOINER_MARKETING_LIST_ID);
            if ($apiKey && $apiSecret && $listId) {
                $data = array(
                    'email' => $subscriber->getSubscriberEmail(),
                    'list_id' => $listId
                );
                $this->addToList($apiKey, $apiSecret, $siteId, $data);
            }
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function subscribeCustomerAdmin(Varien_Event_Observer $observer)
    {
        if (Mage::getStoreConfig(Rejoiner_Acr_Helper_Data::XML_PATH_REJOINER_MARKETING_PERMISSIONS)) {
            $order = $observer->getOrder();
            $apiKey = $this->getRejoinerApiKey();
            $apiSecret = utf8_encode($this->getRejoinerApiSecret());
            $siteId = Mage::getStoreConfig(self::XML_PATH_REJOINER_API_SITE_ID);
            $listId = Mage::getStoreConfig(Rejoiner_Acr_Helper_Data::XML_PATH_REJOINER_MARKETING_LIST_ID);
            if ($apiKey && $apiSecret) {
                $this->convert($apiKey, $apiSecret, $siteId, $order->getCustomerEmail());
                if ($listId) {
                    $data = array(
                        'email' => $order->getCustomerEmail(),
                        'list_id' => $listId,
                        'first_name' => $order->getCustomerFirstname()
                    );
                    $this->addToList($apiKey, $apiSecret, $siteId, $data);
                }
            }
        }
    }

}