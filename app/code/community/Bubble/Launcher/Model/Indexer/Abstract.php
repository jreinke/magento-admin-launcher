<?php

abstract class Bubble_Launcher_Model_Indexer_Abstract extends Mage_Core_Model_Abstract
{
    abstract protected function _buildIndexData();

    final public function getIndexData()
    {
        $cacheId = $this->getCacheId();
        if (false !== ($data = Mage::app()->loadCache($cacheId))) {
            $data = unserialize($data);
        } else {
            $data = $this->_buildIndexData();
            Mage::app()->saveCache(serialize($data), $cacheId);
        }

        return $data;
    }

    public function canIndex()
    {
        return true;
    }

    public function clearCache()
    {
        return Mage::app()->removeCache($this->getCacheId());
    }

    public function getCacheId()
    {
        return get_class($this) . '_' . $this->_getSession()->getSessionId();
    }

    protected function _getSession()
    {
        return Mage::getSingleton('adminhtml/session');
    }

    protected function _getUrl($action, $params = array())
    {
        return Mage::helper('adminhtml')->getUrl($action, $params);
    }

    protected function _isAllowed($resource, $privilege = null)
    {
        return Mage::getSingleton('admin/session')->isAllowed($resource, $privilege);
    }

    protected function _prepareData($title, $text, $url)
    {
        return array(
            'title' => (string) $title,
            'text'  => (string) $text,
            'url'   => (string) $url,
        );
    }
}