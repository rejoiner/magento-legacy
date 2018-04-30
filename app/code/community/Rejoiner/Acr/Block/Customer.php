<?php
class Rejoiner_Acr_Block_Customer extends Rejoiner_Acr_Block_Base
{
    protected $customer;

    /**
     * @return Mage_Customer_Model_Customer
     */
    protected function getCurrentCustomer()
    {
        if (!$this->customer) {
            $this->customer = Mage::getSingleton('customer/session')->getCustomer();
        }
        return $this->customer;
    }

    /**
     * @return string
     */
    public function getCustomerInfo()
    {
        $customerData = array(
            'age'    => $this->getCustomerAge(),
            'gender' => $this->getGender(),
            'en'     => substr(Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE,0),0,2),
            'name'   => $this->getCurrentCustomer()->getFirstname()

        );
        return json_encode($customerData);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->getCurrentCustomer()->getId()) {
            $html = parent::_toHtml();
        } else {
            $html = '';
        }

        return $html;
    }

    /**
     * @return int
     */
    protected function getCustomerAge()
    {
        $age = 0;
        if ($dob = $this->getCurrentCustomer()->getDob()) {
            $birthdayDate = new DateTime($dob);
            $now = new DateTime();
            $interval = $now->diff($birthdayDate);
            $age = $interval->y;
        }
        return $age;
    }

    /**
     * @return string
     */
    protected function getGender()
    {
        $genderText = $this->getCurrentCustomer()
          ->getResource()
          ->getAttribute('gender')
          ->getSource()
          ->getOptionText($this->getCurrentCustomer()->getData('gender'));

        return $genderText? $genderText : '';
    }

    /**
     * @return string
     */
    public function getCustomerEmail()
    {
        return str_replace('\\/', '/', json_encode(array('email' => $this->getCurrentCustomer()->getEmail())));
    }

}