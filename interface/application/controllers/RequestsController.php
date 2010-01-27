<?php

class RequestsController extends Zend_Controller_Action
{
    protected $_storage;

    public function init()
    {
        $this->_storage = $this->getInvokeArg('bootstrap')->getResource('Storage');
    }

    public function indexAction()
    {
        $this->view->application = $this->_getParam('application');
        $this->view->requests = $this->_storage->getAverageRequests($this->_getParam('application'));
    }

    public function averageAction()
    {
        $this->view->application = $this->_getParam('application');
        $requests = $this->_storage->getRequests($this->_getParam('application'));

        $this->view->data = TimeRangerUi_Requests::average($requests);
    }

    public function showAction()
    {
        $this->view->application = $this->_getParam('application');
        $this->view->request     = $this->_getParam('request');

        $requests = $this->_storage->getRequests($this->_getParam('application'), $this->_getParam('request'));

        $allData = array();
        $metrics = array();

        $requestsCount = count($requests);

        foreach($requests as $index => $request) {
            $data = $request->data;

            if ($index == 0) {
                foreach ($data["main()"] as $metric => $val) {
                    if ($metric != "pmu") {
                        if (isset($val)) {
                            $metrics[] = $metric;
                        }
                    }
                }
            }

            foreach ($data as $parentChild => $info) {
                if (!isset($allData[$parentChild])) {
                    $allData[$parentChild] = array();
                    foreach ($metrics as $metric) {
                        $allData[$parentChild][$metric] = $info[$metric];
                    }
                } else {
                    foreach ($metrics as $metric) {
                        $allData[$parentChild][$metric] += $info[$metric];
                    }
                }
            }
        }

        foreach ($allData as $parentChild => &$info) {
            foreach ($info as $metric => &$value) {
                $value = ($value / $requestsCount);
            }
        }

        print_r($data);
    }

    public function cleanupAction()
    {
        $this->_storage->cleanupRequests($this->_getParam('application'));

        $this->_redirect('/');
        //$this->_helper->redirector->setGoToRoute(array('application' => $this->_getParam('application')), 'requests');
    }
}