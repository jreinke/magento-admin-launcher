<?php

class Bubble_Launcher_Model_Indexer_Action extends Bubble_Launcher_Model_Indexer_Abstract
{
    protected function _buildIndexData()
    {
        $data = array();
        $config = Mage::getSingleton('bubble_launcher/launcher')->getConfig();
        foreach ($config['actions'] as $action) {
            if (!isset($action['acl']) || $this->_isAllowed($action['acl'])) {
                $title  = $this->_getLabel($action['title']);
                $text   = $this->_getLabel($action['text']);
                $params = isset($action['params']) ? $action['params'] : array();
                $url    = $this->_getUrl($action['action'], $params);
                $data[] = $this->_prepareData($title, $text, $url);
            }
        }

        return $data;
    }

    protected function _getLabel($label)
    {
        if (is_string($label)) {
            return $label;
        }

        $module = @$label['@']['module'];
        $helper = Mage::helper($module);
        if ($helper) {
            $label = $helper->__($label[0]);
        } else {
            $label = $label[0];
        }

        return $label;
    }

    public function canIndex()
    {
        return Mage::getStoreConfigFlag('bubble_launcher/general/index_actions');
    }
}