<?php
/**
 * @category    Bubble
 * @package     Bubble_Launcher
 * @version     1.0.0
 * @copyright   Copyright (c) 2014 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_Launcher_Model_Indexer_Promotion extends Bubble_Launcher_Model_Indexer_Abstract
{
    protected function _buildIndexData()
    {
        $data = array();

        // Catalog Price Rules
        if ($this->_isAllowed('promo/catalog')) {
            $collection = Mage::getModel('catalogrule/rule')
                ->getResourceCollection();
            $titleLabel = Mage::helper('bubble_launcher')->__('Catalog Rule');
            $menuLabel = Mage::helper('adminhtml')->__('Catalog Price Rules');
            foreach ($collection as $promo) {
                /** @var $promo Mage_CatalogRule_Model_Rule */
                $title  = sprintf('%s > %s', $titleLabel, $promo->getName());
                $text   = sprintf('%s > %s', $menuLabel, $promo->getName());
                $url    = $this->_getUrl('adminhtml/promo_catalog/edit', array('id' => $promo->getId()));
                $data[] = $this->_prepareData($title, $text, $url);
            }
        }

        // Sopping Cart Price Rules
        if ($this->_isAllowed('promo/quote')) {
            $collection = Mage::getModel('salesrule/rule')
                ->getResourceCollection();
            $titleLabel = Mage::helper('bubble_launcher')->__('Shopping Cart Rule');
            $menuLabel = Mage::helper('adminhtml')->__('Shopping Cart Price Rules');
            foreach ($collection as $promo) {
                /** @var $promo Mage_SalesRule_Model_Rule */
                $title  = sprintf('%s > %s', $titleLabel, $promo->getName());
                $text   = sprintf('%s > %s', $menuLabel, $promo->getName());
                $url    = $this->_getUrl('adminhtml/promo_quote/edit', array('id' => $promo->getId()));
                $data[] = $this->_prepareData($title, $text, $url);
            }
        }

        return $data;
    }

    public function canIndex()
    {
        return Mage::getStoreConfigFlag('bubble_launcher/general/index_promotions');
    }
}