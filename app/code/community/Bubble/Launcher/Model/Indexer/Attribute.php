<?php

class Bubble_Launcher_Model_Indexer_Attribute extends Bubble_Launcher_Model_Indexer_Abstract
{
    protected function _buildIndexData()
    {
        $data = array();
        $collection = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addVisibleFilter();
        $menuLabel = Mage::helper('adminhtml')->__('Manage Attributes');
        foreach ($collection as $attribute) {
            /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
            $title  = sprintf('%s (%s)', $attribute->getStoreLabel(), $attribute->getAttributeCode());
            $text   = sprintf('%s > %s (%s)',
                $menuLabel, $attribute->getStoreLabel(), $attribute->getAttributeCode());
            $url    = $this->_getUrl('adminhtml/catalog_product_attribute/edit',
                array('attribute_id' => $attribute->getId()));
            $data[] = $this->_prepareData($title, $text, $url);
        }

        return $data;
    }

    public function canIndex()
    {
        return $this->_isAllowed('catalog/attributes/attributes') &&
            Mage::getStoreConfigFlag('bubble_launcher/general/index_attributes');
    }
}