<?php

class Bubble_Launcher_Model_Observer
{
    public function initConfigState()
    {
        $fieldset = Mage::app()->getRequest()->getParam('fieldset');
        if ($fieldset) {
            $adminUser = Mage::getSingleton('admin/session')->getUser();
            $extra = $adminUser->getExtra();
            if (!is_array($extra)) {
                $extra = array();
            }
            if (!isset($extra['configState'])) {
                $extra['configState'] = array();
            }
            $extra['configState'][$fieldset] = 1;
            $adminUser->setExtra($extra)
                ->saveExtra($extra);
        }
    }

    public function onAdminLogin(Varien_Event_Observer $observer)
    {
        $success = $observer->getEvent()->getResult();
        if ($success) {
            Mage::getSingleton('bubble_launcher/launcher')->clearCache();
        }
    }

    public function onChangeLocale()
    {
        Mage::getSingleton('bubble_launcher/launcher')->clearCache();
    }

    public function clearProductsIndexCache()
    {
        Mage::getSingleton('bubble_launcher/indexer_product')->clearCache();
    }

    public function clearCategoriesIndexCache()
    {
        Mage::getSingleton('bubble_launcher/indexer_category')->clearCache();
    }

    public function clearPromotionsIndexCache()
    {
        Mage::getSingleton('bubble_launcher/indexer_promotion')->clearCache();
    }
}