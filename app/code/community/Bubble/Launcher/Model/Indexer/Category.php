<?php
/**
 * @category    Bubble
 * @package     Bubble_Launcher
 * @version     1.0.0
 * @copyright   Copyright (c) 2014 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_Launcher_Model_Indexer_Category extends Bubble_Launcher_Model_Indexer_Abstract
{
    protected function _buildIndexData()
    {
        $data = array();
        $collection = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToSelect('name')
            ->addFieldToFilter('level', array('gt' => 1));
        $categories = $collection->load()->toArray();
        $menuLabel = Mage::helper('adminhtml')->__('Manage Categories');
        foreach ($categories as $category) {
            $path = array();
            foreach (explode('/', $category['path']) as $categoryId) {
                if (isset($categories[$categoryId])) {
                    $path[] = $categories[$categoryId]['name'];
                }
            }
            $title  = $category['name'];
            $text   = sprintf('%s > %s', $menuLabel, implode(' > ', $path));
            $url    = $this->_getUrl('adminhtml/catalog_category/edit', array('id' => $category['entity_id']));
            $data[] = $this->_prepareData($title, $text, $url);
        }

        return $data;
    }

    public function canIndex()
    {
        return $this->_isAllowed('catalog/categories') &&
            Mage::getStoreConfigFlag('bubble_launcher/general/index_categories');
    }
}