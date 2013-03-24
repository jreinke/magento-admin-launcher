<?php

class Bubble_Launcher_Adminhtml_Bubble_LauncherController extends Mage_Adminhtml_Controller_Action
{
    protected $_publicActions = array('index');

    public function indexAction()
    {
        $data = Mage::getSingleton('bubble_launcher/launcher')->getIndexData();
        $this->getResponse()
            ->setHeader('Content-Type', 'application/json')
            ->setBody(json_encode($data))
            ->sendResponse();
        exit;
    }

    public function clearCacheAction()
    {
        $type = $this->getRequest()->getParam('type');
        if (!empty($type)) {
            $this->_forward('massRefresh', 'cache', null, array('types' => array($type)));
        } else {
            $this->_forward('flushSystem', 'cache');
        }
    }

    public function reindexAllAction()
    {
        $processes = Mage::getSingleton('index/indexer')->getProcessesCollection();
        try {
            foreach ($processes as $process) {
                /* @var $process Mage_Index_Model_Process */
                $process->reindexEverything();
            }
            $count = count($processes);
            $this->_getSession()->addSuccess(
                Mage::helper('index')->__('Total of %d index(es) have reindexed data.', $count)
            );
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addException($e, Mage::helper('index')->__('Cannot initialize the indexer process.'));
        }

        $this->_redirect('adminhtml/process/list');
    }
}