<?php
class Rejoiner_Acr_Block_Product extends Rejoiner_Acr_Block_Base
{
    /**
     * @return string
     */
    public function getCurrentProductInfo()
    {
        $product        = Mage::registry('current_product');
        $stocklevel = (int)Mage::getModel('cataloginventory/stock_item')
            ->loadByProduct($product)->getQty();
        $imageHelper    = Mage::helper('catalog/image');
        $rejoinerHelper = Mage::helper('rejoiner_acr');
        $mediaUrl       = Mage::getBaseUrl('media');
        $thumbnail      = 'no_selection';
        $categories     = array();
        $categoryCollection = Mage::getModel('catalog/category')->getCollection();
        $categoryCollection->addAttributeToSelect('name');
        $categoryCollection->addFieldToFilter('entity_id', array('in' => $product->getCategoryIds()));
        /** @var Mage_Catalog_Model_Category $category */
        foreach ($categoryCollection as $category) {
            $categories[] = $category->getName();
        }

        if ($product->getData('thumbnail') && ($product->getData('thumbnail') != 'no_selection')) {
            $thumbnail = $product->getData('thumbnail');
        }
        $io = new Varien_Io_File();
        if (!$io->fileExists(Mage::getBaseDir('media') . '/catalog/product' . $thumbnail)) {
            $thumbnail = 'no_selection';
        }
        // use placeholder image if nor simple nor configurable products does not have images
        if ($thumbnail == 'no_selection') {
            $imageHelper->init($product, 'thumbnail');
            $image = Mage::getDesign()->getSkinUrl($imageHelper->getPlaceholder());
        } elseif ($imagePath = $rejoinerHelper->resizeImage($thumbnail)) {
            $image = str_replace(Mage::getBaseDir('media') . '/', $mediaUrl, $imagePath);
        } else {
            $image = (string) $mediaUrl . 'catalog/product' . $thumbnail;
        }

        $productData = array(
            'name'        => (string) $product->getName(),
            'image_url'   => (string) $image,
            'price'       => $this->_convertPriceToCents((string) $product->getPrice()),
            'product_id'  => (string) $product->getSku(),
            'product_url' => (string) $product->getProductUrl(),
            'category'    => $categories,
            'stock'       => $stocklevel
        );
        return str_replace('\\/', '/', json_encode($productData));
    }
}