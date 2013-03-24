<?php

class Bubble_Launcher_Model_Launcher extends Mage_Core_Model_Abstract
{
    const CONFIG_CACHE_ID = 'bubble_launcher_config';

    protected $_config;

    protected $_indexers = array();

    protected $_scopes = array();

    protected function _construct()
    {
        $this->_initConfig();
        $this->_loadIndexers();
    }

    protected function _initConfig()
    {
        $cacheId = self::CONFIG_CACHE_ID;
        $data = Mage::app()->loadCache($cacheId);
        if (false !== $data) {
            $data = unserialize($data);
        } else {
            $xml = Mage::getConfig()->loadModulesConfiguration('launcher.xml')->getNode();
            $data = $xml->asArray();
            Mage::app()->saveCache(serialize($data), $cacheId);
        }

        $this->_config = $data;

        return $this;
    }

    protected function _loadIndexers()
    {
        $indexers = $this->_config['indexers'];
        foreach ($indexers as $scope => $indexer) {
            $model = Mage::getSingleton($indexer['class']);
            if ($model instanceof Bubble_Launcher_Model_Indexer_Abstract && $model->canIndex()) {
                $this->_indexers[$scope] = $model;
                $this->_scopes[$scope] = Mage::helper('bubble_launcher')->__($scope);
            }
        }

        return $this;
    }

    public function clearCache()
    {
        Mage::app()->removeCache(self::CONFIG_CACHE_ID);
        foreach ($this->_indexers as $indexer) {
            $indexer->clearCache();
        }

        return $this;
    }

    public function getConfig()
    {
        return $this->_config;
    }

    public function getIndexData()
    {
        $data = array(
            'scopes' => $this->_scopes,
            'pages'  => array(),
        );
        foreach ($this->_indexers as $scope => $indexer) {
            $pages = $indexer->getIndexData();
            foreach ($pages as &$page) {
                $page['scope'] = $scope;
            }
            unset($page);
            $data['pages'] = array_merge($data['pages'], $pages);
        }

        return $data;
    }
}