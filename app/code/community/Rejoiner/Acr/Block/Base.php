<?php

class Rejoiner_Acr_Block_Base extends Mage_Core_Block_Template
{
    const XML_PATH_REJOINER_TRACK_PRICE_WITH_TAX = 'checkout/rejoiner_acr/price_with_tax';

    /**
     * @return array
     */
    public function getCartItems()
    {
        $items = array();
        if ($quote = $this->_getQuote()) {
            $displayPriceWithTax = $this->getTrackPriceWithTax();
            $mediaUrl            = Mage::getBaseUrl('media');
            $quoteItems          = $quote->getAllItems();
            /** @var Rejoiner_Acr_Helper_Data $rejoinerHelper */
            $rejoinerHelper = Mage::helper('rejoiner_acr');
            $parentToChild  = array();
            $categories     = array();
            /** @var Mage_Sales_Model_Quote_Item $item */
            foreach ($quoteItems as $item) {
                /** @var Mage_Sales_Model_Quote_Item $parent */
                if ($parent = $item->getParentItem()) {
                    if ($parent->getProductType() == Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE) {
                        $parentToChild[$parent->getId()] = $item;
                    }
                }
                $categories = array_merge($categories, $item->getProduct()->getCategoryIds());
            }

            /** @var Mage_Catalog_Model_Resource_Category_Collection $categoryCollection */
            $categoryCollection = Mage::getModel('catalog/category')->getCollection();
            $categoryCollection
                ->addAttributeToSelect('name')
                ->addFieldToFilter('entity_id', array('in' => array_unique($categories)));
            $imageHelper = Mage::helper('catalog/image');

            foreach ($quoteItems as $item) {
                if ($item->getParentItem()) {
                    continue;
                }
                $product = $item->getProduct();
                // Collection is loaded only once, so it is ok to do $categoryCollection->getItems() inside the loop
                // From the other hand we won't ever get here if not needed
                $productCategories = $rejoinerHelper->getProductCategories($product, $categoryCollection->getItems());
                $thumbnail = 'no_selection';

                // get thumbnail from configurable product
                if ($product->getData('thumbnail') && ($product->getData('thumbnail') != 'no_selection')) {
                    $thumbnail = $product->getData('thumbnail');
                    // or try finding it in the simple one
                } elseif ($item->getProductType() == Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE) {
                    /** @var Mage_Sales_Model_Quote_Item $simpleItem */
                    $simpleItem = $parentToChild[$item->getId()];
                    $simpleProduct = $simpleItem->getProduct();
                    if ($simpleProduct->getData('thumbnail') && ($simpleProduct->getData('thumbnail') != 'no_selection')) {
                        $thumbnail = $simpleProduct->getData('thumbnail');
                    }
                }
                if (!file_exists(Mage::getBaseDir('media') . '/catalog/product' . $thumbnail)) {
                    $thumbnail = 'no_selection';
                }
                // use placeholder image if nor simple nor configurable products does not have images
                if ($thumbnail == 'no_selection') {
                    $imageHelper->init($product, 'thumbnail');
                    $image = Mage::getDesign()->getSkinUrl($imageHelper->getPlaceholder());
                } elseif($imagePath = $rejoinerHelper->resizeImage($thumbnail)) {
                    $image = str_replace(Mage::getBaseDir('media') . '/', $mediaUrl, $imagePath);
                } else {
                    $image = $mediaUrl . 'catalog/product' . $thumbnail;
                }

                if ($displayPriceWithTax) {
                    $prodPrice = $item->getPriceInclTax();
                    $rowTotal  = $item->getRowTotalInclTax();
                } else {
                    $prodPrice = $item->getBaseCalculationPrice();
                    $rowTotal  = $item->getBaseRowTotal();
                }
                $newItem = array(
                    'name'        => $item->getName(),
                    'image_url'   => $image,
                    'price'       => $this->_convertPriceToCents($prodPrice),
                    'product_id'  => (string) $item->getSku(),
                    'product_url' => (string) $item->getProduct()->getProductUrl(),
                    'item_qty'    => $item->getQty(),
                    'qty_price'   => $this->_convertPriceToCents($rowTotal),
                    'category'    => $productCategories
                );
                $items[] = $newItem;
            }
        }
        return $items;
    }



    /**
     * @param $price float
     * @return float
     */
    protected function _convertPriceToCents($price) {
        return round($price * 100);
    }

    /**
     * @return bool
     */
    protected function getTrackPriceWithTax()
    {
        return Mage::getStoreConfig(self::XML_PATH_REJOINER_TRACK_PRICE_WITH_TAX);
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote()
    {
        /** @var Mage_Checkout_Model_Session $session */
        $session = Mage::getSingleton('checkout/session');
        return $session->getQuote();
    }
}