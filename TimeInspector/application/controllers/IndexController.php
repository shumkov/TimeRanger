<?php

class IndexController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $storage = $this->getInvokeArg('bootstrap')->getResource('Storage');
        $this->view->applications = $storage->getApplications();
    }
}