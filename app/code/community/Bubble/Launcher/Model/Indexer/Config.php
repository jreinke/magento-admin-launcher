<?php

class Bubble_Launcher_Model_Indexer_Config extends Bubble_Launcher_Model_Indexer_Abstract
{
    protected function _buildIndexData()
    {
        $data = array();
        $sections = Mage::getSingleton('adminhtml/config')->getSections()->asArray();
        $menuLabel = Mage::helper('adminhtml')->__('Configuration');
        foreach ($sections as $section => $sectionData) {
            $module = false;
            if (isset($sectionData['@']) && isset($sectionData['@']['module'])) {
                $module = $sectionData['@']['module'];
            }
            $sectionLabel = (string) $sectionData['label'];
            if ($module) {
                $sectionLabel = Mage::helper($module)->__($sectionLabel);
            }
            foreach ($sectionData['groups'] as $group => $groupData) {
                if (!$this->_isAllowed('system/config/' . $group)) {
                    continue;
                }
                $groupLabel = (string) @$groupData['label'];
                if (empty($groupLabel)) {
                    continue;
                }
                if ($module) {
                    $groupLabel = Mage::helper($module)->__($groupLabel);
                }
                $fieldset   = $section . '_' . $group;
                $title      = $groupLabel;
                $text       = sprintf('%s > %s > %s', $menuLabel, $sectionLabel, $groupLabel);
                $url        = $this->_getUrl('adminhtml/system_config/edit',
                    array('section' => $section, 'fieldset' => $fieldset));
                $data[] = $this->_prepareData($title, $text, $url);
                if (isset($groupData['fields']) && is_array($groupData['fields'])) {
                    foreach ($groupData['fields'] as $fieldData) {
                        $fieldLabel = (string) @$fieldData['label'];
                        if (empty($fieldLabel)) {
                            continue;
                        }
                        if ($module) {
                            $fieldLabel = Mage::helper($module)->__($fieldLabel);
                        }
                        $title  = sprintf('%s > %s > %s',
                            $sectionLabel, $groupLabel, $fieldLabel);
                        $text   = sprintf('%s > %s > %s > %s',
                            $menuLabel, $sectionLabel, $groupLabel, $fieldLabel);
                        $url    = $this->_getUrl('adminhtml/system_config/edit',
                            array('section' => $section, 'fieldset' => $fieldset));
                        $data[] = $this->_prepareData($title, $text, $url);
                    }
                }
            }
        }

        return $data;
    }

    public function canIndex()
    {
        return $this->_isAllowed('system/config') &&
            Mage::getStoreConfigFlag('bubble_launcher/general/index_config');
    }
}