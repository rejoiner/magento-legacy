<?php

class Rejoiner_Acr_Model_Container_Base extends Enterprise_PageCache_Model_Container_Abstract
{
    public $childBlocks = array(
        'rejoiner_customer',
    );

    protected function _getIdentifier()
    {
        $cacheId = $this->_getCookieValue(Enterprise_PageCache_Model_Cookie::COOKIE_CUSTOMER, '')
            . '_'
            . $this->_getCookieValue(Enterprise_PageCache_Model_Cookie::COOKIE_CUSTOMER_LOGGED_IN, '');
        return $cacheId;
    }
    /**
     * Retrieve cache id
     *
     * @return string
     */
    protected function _getCacheId()
    {
        return 'REJOINER_ACR_BASE_' . md5($this->_placeholder->getAttribute('cache_id') . $this->_getIdentifier());
    }
    /**
     * Render block that was not cached
     *
     * @return false|string
     */
    protected function _renderBlock()
    {
        $block = $this->_getPlaceHolderBlock();
        foreach($this->childBlocks as $child) {
            $block->setChild($child, $this->_getChildBlock($child));
        }
        Mage::dispatchEvent('render_block', array('block' => $block, 'placeholder' => $this->_placeholder));
        return $block->toHtml();;
    }

    /**
     * Get child Block
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _getChildBlock($name)
    {
        return $this->_getLayout('default')->getBlock($name);
    }

    protected function _saveCache($data, $id, $tags = array(), $lifetime = null)
    {
        return false;
    }
}