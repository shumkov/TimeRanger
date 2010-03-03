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

        $this->view->data = TimeInspector_Requests::average($requests);
    }

    public function showAction()
    {
        $this->view->application = $this->_getParam('application');
        $this->view->request     = $this->_getParam('request');

        $requests = $this->_storage->getRequests($this->_getParam('application'), $this->_getParam('request'));

        $this->view->data = TimeInspector_Requests::average($requests);
    }

    public function cleanupAction()
    {
        $this->_storage->cleanupRequests($this->_getParam('application'));

        $this->_helper->redirector->setGoToRoute(array('application' => $this->_getParam('application')), 'requests');
    }
}