<?php

require_once 'Zend/Controller/Plugin/Abstract.php';
require_once 'Zend/Controller/Request/Http.php';

class TimeRanger_Zend_Controller_Plugin_RequestName extends Zend_Controller_Plugin_Abstract
{
    protected $_firstDispatch = true;

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        if ($this->_firstDispatch) {
            $requestName = $request->getControllerName() . '::' . $request->getActionName();

            $dashToCamelCase = new Zend_Filter_Word_DashToCamelCase();
            $requestName = $dashToCamelCase->filter($requestName);

            TimeRanger_Profiler::setRequestName($requestName);

            $this->_firstDispatch = false;
        }
    }
}