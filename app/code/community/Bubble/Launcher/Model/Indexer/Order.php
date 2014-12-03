<?php
/**
 * @category    Bubble
 * @package     Bubble_Launcher
 * @version     1.0.0
 * @copyright   Copyright (c) 2014 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_Launcher_Model_Indexer_Order extends Bubble_Launcher_Model_Indexer_Abstract
{
    protected function _buildIndexData()
    {
        $data = array();
        $collection = Mage::getModel('sales/order')->getCollection();
        $menuLabel = Mage::helper('adminhtml')->__('Orders');
        foreach ($collection as $order) {
            /** @var $order Mage_Sales_Model_Order */
            $title  = sprintf('%s > %s (%s)',
                $order->getIncrementId(), $order->getCustomerName(), $order->getCustomerEmail());
            $text   = sprintf('%s > %s > %s (%s)',
                $menuLabel, $order->getIncrementId(), $order->getCustomerName(), $order->getCustomerEmail());
            $url    = $this->_getUrl('adminhtml/sales_order/view', array('order_id' => $order->getId()));
            $data[] = $this->_prepareData($title, $text, $url);
        }

        return $data;
    }

    public function canIndex()
    {
        return $this->_isAllowed('sales/order', 'edit') &&
            Mage::getStoreConfigFlag('bubble_launcher/general/index_orders');
    }
}