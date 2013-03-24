<?php

class Bubble_Launcher_Model_Indexer_Menu extends Bubble_Launcher_Model_Indexer_Abstract
{
    protected function _buildIndexData()
    {
        $menu = Mage::app()->getLayout()->createBlock('adminhtml/page_menu')->getMenuArray();
        array_walk_recursive($menu, function(&$item, $key) {
            $pattern = '#' . Mage_Adminhtml_Model_Url::SECRET_KEY_PARAM_NAME . '/\$([^\/].*)/([^\$].*)\$#';
            if ($key === 'url' && $item !== '#') {
                $item = preg_replace_callback($pattern, function($matches) {
                    return Mage_Adminhtml_Model_Url::SECRET_KEY_PARAM_NAME . '/' .
                        Mage::getSingleton('adminhtml/url')->getSecretKey($matches[1], $matches[2]);
                }, $item);
            }
        });

        $data = $this->_buildRecursiveData($menu);

        return $data;
    }

    protected function _buildRecursiveData($menu, $path = array())
    {
        $data = array();
        foreach ($menu as $item) {
            $url = $item['url'];
            if ($url !== '#') {
                $title  = $item['label'];
                $text   = implode(' > ', array_merge($path, array($title)));
                $data[] = $this->_prepareData($title, $text, $url);
            }
            if (isset($item['children'])) {
                $path[] = $item['label'];
                $data = array_merge($data, $this->_buildRecursiveData($item['children'], $path));
                array_pop($path);
            }
        }

        return $data;
    }

    public function canIndex()
    {
        return Mage::getStoreConfigFlag('bubble_launcher/general/index_menu');
    }
}