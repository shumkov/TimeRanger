<?php

class TimeRanger_Storage_File implements TimeRanger_Storage_Interface
{
    protected $_dir;

    public function __construct(array $options = array())
    {
        if (!isset($options['dir'])) {
            $options['dir'] = ini_get("xhprof.output_dir");
            if (empty($options['dir'])) {
                require_once 'TimeRanger/Exception.php';
                throw new TimeRanger_Exception('Profile storage directory must be set');
            }
        }

        $this->setDirectory($options['dir']);
    }

    public function save($data, $elapsedTime)
    {

        $storage = new Geometria_Profile_Storage();
    	$dir = $storage->getDir();

        if (is_dir($dir)) {
        	$this->view->servers = array();
            foreach (new DirectoryIterator($dir) as $file) {
                if (!$file->isDot() && !$file->isDir()) {
                    list($serverName, $controllerName, $actionName, $time) = explode('-', $file->getFilename());
                    if (!isset($this->view->servers[$serverName])) {
                    	$this->view->servers[$serverName] = array();
                    }
                    if (!isset($this->view->servers[$serverName][$controllerName])) {
                        $this->view->servers[$serverName][$controllerName] = array();
                    }

                    $actionName
                    $time
                    $this->view->servers[$serverName][$controllerName][]
                }
            }
        } else {
            require_once 'Zend/Acl/Exception.php';
            throw new Zend_Controller_Action_Exception("Can't read directory $dir");
        }
        if (isset($_GET['show'])) {
            $controllerName = $_GET['show'];
        } else {
            $controllerName = 'index';
        }

        if (isset($_GET['action'])) {
            $actionName = $_GET['action'];
        } else {
            $actionName = 'index';
        }

        $file = $this->_dir . '/' . $_SERVER['SERVER_NAME'] . '-' . $controllerName . '-' . $actionName . '-' . $time . '-' . uniqid();

        $combinedData = array('data' => $data, 'get' => $_GET, 'post' => $_POST);
        $data = serialize($combinedData);

        $result = @file_put_contents($file, $data);

        if (!$result) {
            require_once 'Geometria/Profile/Exception.php';
            throw new Geometria_Profile_Exception("Can't store profile data to $file");
        }
    }

    public function load()
    {

    }

    public function setDirectory($dir = null)
    {
        $this->_dir = $dir;
        
        return $this;
    }

    public function getDirectory()
    {
        return $this->_dir;
    }
}