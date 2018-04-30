<?php

class Rejoiner_Acr_Block_Adminhtml_Newsletter_Subscriber_Grid extends Mage_Adminhtml_Block_Newsletter_Subscriber_Grid
{
    /**
     * Constructor
     *
     * Set main configuration of grid
     */
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * @inherit
     */
    public function _prepareColumns()
    {
        if ($this->_isActive()) {
                $helper = Mage::helper('rejoiner_acr');
                self::addColumnAfter(
                    'added_to_rejoiner',
                    array(
                            'header'  => $helper->__('Added To Rejoiner'),
                            'align'   => 'center',
                            'width'   => '80px',
                            'type'    => 'options',
                            'options' => array(
                                0 => $helper->__('No'),
                                1 => $helper->__('Yes')
                            ),
                            'default' => '0',
                            'index'   => 'added_to_rejoiner'
                        ), 'store'
                );
        }
        return parent::_prepareColumns();
    }

    /**
     * @return bool
     */
    protected function _isActive()
    {
        return (bool) Mage::getStoreConfig(Rejoiner_Acr_Helper_Data::XML_PATH_REJOINER_MARKETING_PERMISSIONS);
    }

}
