<?php

class Bubble_Launcher_Model_Indexer_Product extends Bubble_Launcher_Model_Indexer_Abstract
{
    protected function _buildIndexData()
    {
        $data = array();
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name');
        $menuLabel = Mage::helper('catalog/product')->__('Manage Products');
        foreach ($collection as $product) {
            /** @var $product Mage_Catalog_Model_Product */
            $title  = sprintf('%s (%s)', $product->getName(), $product->getSku());
            $text   = sprintf('%s > %s (%s)', $menuLabel, $product->getName(), $product->getSku());
            $url    = $this->_getUrl('adminhtml/catalog_product/edit', array('id' => $product->getId()));
            $data[] = $this->_prepareData($title, $text, $url);
        }

        return $data;
    }

    public function canIndex()
    {
        return $this->_isAllowed('catalog/products') &&
            Mage::getStoreConfigFlag('bubble_launcher/general/index_products');
    }
}