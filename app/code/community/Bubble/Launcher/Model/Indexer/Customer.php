<?php

class Bubble_Launcher_Model_Indexer_Customer extends Bubble_Launcher_Model_Indexer_Abstract
{
    protected function _buildIndexData()
    {
        $data = array();
        $collection = Mage::getModel('customer/customer')->getCollection()
            ->addAttributeToSelect('firstname')
            ->addAttributeToSelect('lastname')
            ->addAttributeToSelect('email');
        $menuLabel = Mage::helper('adminhtml')->__('Manage Customers');
        foreach ($collection as $customer) {
            /** @var $order Mage_Customer_Model_Customer */
            $name   = trim($customer->getFirstname() . ' ' . $customer->getLastname());
            $title  = $name ? $name : $customer->getEmail();
            $text   = sprintf('%s > %s (%s)', $menuLabel , $name, $customer->getEmail());
            $url    = $this->_getUrl('adminhtml/customer/edit', array('id' => $customer->getId()));
            $data[] = $this->_prepareData($title, $text, $url);
        }

        return $data;
    }

    public function canIndex()
    {
        return $this->_isAllowed('customer/manage') &&
            Mage::getStoreConfigFlag('bubble_launcher/general/index_customers');
    }
}