<?php

require_once 'TimeRanger/Storage/Interface.php';

abstract class TimeRanger_Storage_Abstract implements TimeRanger_Storage_Interface
{
	protected $_options = array();

	public function __construct(array $options = array())
	{
        // Apply default options
    	$options = array_merge($this->_options, $options);

        $this->setOptions($options);
	}

    /**
     * Set options array
     * 
     * @param array $options Options (see $_options description)
     * @return TimeRanger_Storage_Abstract
     */
    public function setOptions(array $options)
    {
        foreach($options as $name => $value) {
            if (method_exists($this, "set$name")) {
                call_user_func(array($this, "set$name"), $value);
            } else {
                $this->setOption($name, $value);
            }
        }

        return $this;
    }

    /**
     * Set option
     * 
     * @throws TimeRanger_Exception
     * @param string $name Name of option
     * @param mixed $value Value of option
     * @return TimeRanger_Storage_Abstract
     */
    public function setOption($name, $value)
    {
        if (!array_key_exists($name, $this->_options)) {
            require_once 'TimeRanger/Exception.php';
            throw new TimeRanger_Exception("Unknown option '$name'");
        }

        $this->_options[$name] = $value;

        return $this;
    }

    /**
     * Get option
     * 
     * @throws TimeRanger_Exception 
     * @param string $name Name of option
     * @return mixed
     */
    public function getOption($name)
    {
        if (!array_key_exists($name, $this->_options)) {
            require_once 'TimeRanger/Exception.php';
            throw new TimeRanger_Exception("Unknown option '$name'");
        }

        return $this->_options[$name];
    }

    public static function factory($storage, array $options = array())
    {
        if (in_array($storage, array('file', 'db'))) {
            $storage = ucfirst($storage);
            require_once "TimeRanger/Storage/$storage.php";
            $className = "TimeRanger_Storage_$storage";
            $storage = new $className($options);
        } else if (!is_object($storage)) {
            if (!@class_exists($storage)) {
                require_once 'TimeRanger/Exception.php';
                throw new TimeRanger_Exception("Storage '{$storage}' not found. You need include it before or setup autoload.");
            }
            $storage = new $storage($this->_options['storageOptions']);
        }

        if (!$storage instanceof TimeRanger_Storage_Interface) {
            require_once 'TimeRanger/Exception.php';
            throw new TimeRanger_Exception("'" . get_class($storage) . "' must implement TimeRanger_Storage_Interface");
        }

        return $storage;
    }
}