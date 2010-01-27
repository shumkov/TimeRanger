<?php

class TimeRanger_Profiler
{
    protected static $_enabled = false;

    protected static $_samplingMode = false;

    protected static $_startTime;
    
    protected static $_storage;

    protected static $_instance;

    protected static $_protectConstruct = true;

    protected static $_options = array(
        'applicationName'      => null,
        'requestName'          => null,
        'cpu'                  => true,
        'memory'               => true,
        'bulitins'             => false,
        'ignoreFunctions'      => array('call_user_func', 'call_user_func_array'),
        'storage'              => 'db',
        'storageOptions'       => array(),
    );

    public static function start($probability = false)
    {
        if (self::$_enabled) {
            require_once 'TimeRanger/Exception.php';
            throw new TimeRanger_Exception('Profiler already started');
        }

        if ($probability !== false && !is_integer($probability) && $probability <= 0) {
            require_once 'TimeRanger/Exception.php';
            throw new TimeRanger_Exception('Probability must be a positive integer or false');
        }

        if (!self::$_instance) {
            if (!extension_loaded('xhprof')) {
                require_once 'TimeRanger/Exception.php';
                throw new TimeRanger_Exception("The xhprof extension is required: http://pecl.php.net/package/xhprof");
            }

            self::$_protectConstruct = false;
            self::$_instance = new self;
            self::$_protectConstruct = true;
        }

        if (!$probability ||  mt_rand(1, $probability) == 1) {
            $flags = 0;
            if (self::$_options['bulitins']) {
                $flags += XHPROF_FLAGS_NO_BUILTINS;
            }
            if (self::$_options['cpu']) {
                $flags += XHPROF_FLAGS_NO_BUILTINS;
            }
            if (self::$_options['memory']) {
                $flags += XHPROF_FLAGS_MEMORY;
            }

            xhprof_enable($flags, array('ignored_functions' => self::$_options['ignoreFunctions']));

            self::$_startTime = microtime(true);
            self::$_enabled = true;

            return true;
        } else {
            return false;
        }
    }

    public static function stop()
    {
        if (!self::$_enabled) {
            require_once 'Geometria/Profile/Exception.php';
            throw new TimeRanger_Exception('Profiler not started');
        }

        if (is_null(self::$_options['applicationName'])) {
            $applicationName = $_SERVER['SERVER_NAME'];
        } else {
            $applicationName = self::$_options['applicationName'];
        }

        if (is_null(self::$_options['requestName'])) {
            $requestName = $_SERVER['REQUEST_URI'];
        } else {
            $requestName = self::$_options['requestName'];
        }

        $data = array(
            'profile' => xhprof_disable(),
            'get'     => $_GET,
            'post'    => $_POST,
        );

        $elapsedTime = microtime(true) - self::$_startTime;

        return self::getStorage()->saveRequest(
            self::getApplicationName(),
            self::getRequestName(),
            $data,
            $elapsedTime
        );
    }

    public static function setApplicationName($name)
    {
        self::$_options['applicationName'] = $name;

        return true;
    }

    public static function getApplicationName()
    {
        if (is_null(self::$_options['applicationName'])) {
            return $_SERVER['SERVER_NAME'];
        } else {
            return self::$_options['applicationName'];
        }
    }

    public static function setRequestName($name)
    {
        self::$_options['requestName'] = $name;

        return true;
    }

    public static function getRequestName()
    {
        if (is_null(self::$_options['requestName'])) {
            return $_SERVER['REQUEST_URI'];
        } else {
            return self::$_options['requestName'];
        }
    }

    public static function setSamplingMode($flag = true)
    {
    	if (!self::$_samplingMode && $flag) {
    		xhprof_sample_enable();

            return true;
    	} else if (self::$_samplingMode && !$flag) {
    		xhprof_sample_disable();

            return true;
    	}

        return false;
    }

    public static function getSamplingMode()
    {
        return self::$_samplingMode;
    }

    public static function setStorage($storage)
    {
        self::$_options['storage'] = $storage;

        return true;
    }

    public static function getStorage()
    {
        if (!self::$_storage) {
            require_once 'TimeRanger/Storage/Abstract.php';
            self::$_storage = TimeRanger_Storage_Abstract::factory(
                self::$_options['storage'],
                self::$_options['storageOptions']
            );
        }

        return self::$_storage;
    }

    /**
     * Set options array
     *
     * @param array $options Options (see $_options description)
     * @return TimeRanger_Profile
     */
    public static function setOptions(array $options)
    {
        foreach($options as $name => $value) {
            if (method_exists($this, "set$name")) {
                call_user_func(array(self, "set$name"), $value);
            } else {
                self::setOption($name, $value);
            }
        }
    }

    /**
     * Set option
     *
     * @throws TimeRanger_Exception
     * @param string $name Name of option
     * @param mixed $value Value of option
     * @return TimeRanger_Profile
     */
    public static function setOption($name, $value)
    {
        if (!array_key_exists($name, self::$_options)) {
        	require_once 'TimeRanger/Exception.php';
            throw new TimeRanger_Exception("Unknown option '$name'");
        }

        self::$_options[$name] = $value;
    }

    /**
     * Get option
     *
     * @throws TimeRanger_Exception
     * @param string $name Name of option
     * @return mixed
     */
    public static function getOption($name)
    {
        if (!array_key_exists($name, self::$_options)) {
        	require_once 'TimeRanger/Exception.php';
            throw new TimeRanger_Exception("Unknown option '$name'");
        }

        return self::$_options[$name];
    }

    /**
     * Instance for stop on destruct
     */
    public function __construct()
    {
        if (self::$_protectConstruct) {
            require_once 'TimeRanger/Exception.php';
            throw new TimeRanger_Exception("Do TimeRanger_Profiler::start() for start profiling");
        }
    }

    /**
     * Ensure stop profile
     */
    public function __destruct()
    {
        self::stop();
    }
}