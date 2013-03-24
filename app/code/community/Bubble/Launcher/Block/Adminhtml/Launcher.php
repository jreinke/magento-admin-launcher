<?php

class Bubble_Launcher_Block_Adminhtml_Launcher extends Mage_Core_Block_Template
{
    public function isEnabled()
    {
        return Mage::getStoreConfigFlag('bubble_launcher/general/enable');
    }

    public function getMinChars()
    {
        $config = Mage::getStoreConfig('bubble_launcher/launcher/min_chars');

        return abs((int) $config);
    }

    public function getMaxResults()
    {
        $config = Mage::getStoreConfig('bubble_launcher/launcher/max_results');

        return abs((int) $config);
    }

    public function getHotkey()
    {
        $config = Mage::getStoreConfig('bubble_launcher/launcher/hotkey');

        return abs((int) $config);
    }

    public function getResetOnHide()
    {
        return Mage::getStoreConfigFlag('bubble_launcher/launcher/reset_on_hide') ? 'true' : 'false';
    }

    public function getUseScope()
    {
        return Mage::getStoreConfigFlag('bubble_launcher/launcher/use_scope') ? 'true' : 'false';
    }

    public function getShowIcon()
    {
        return Mage::getStoreConfigFlag('bubble_launcher/launcher/show_icon') ? 'true' : 'false';
    }

    public function getShowScope()
    {
        return Mage::getStoreConfigFlag('bubble_launcher/launcher/show_scope') ? 'true' : 'false';
    }

    public function getShowText()
    {
        return Mage::getStoreConfigFlag('bubble_launcher/launcher/show_text') ? 'true' : 'false';
    }
}