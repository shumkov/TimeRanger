<?php

class TimeRangerUi_Application_Resource_ZFDebug extends Zend_Application_Resource_ResourceAbstract
{
	public function init()
	{
		$autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->registerNamespace('ZFDebug');

        $plugins = $this->getOptions();

        $options = array(
            'plugins' => $plugins
        );
        
        $bootstrap = $this->getBootstrap();

        // Настройка плагина для адаптера базы данных
        if (in_array('Database', $plugins) && $bootstrap->hasPluginResource('db')) {
            $bootstrap->bootstrap('db');
            $db = $bootstrap->getResource('db');
            $options['plugins']['Database']['adapter'] = $db;
            unset($options['plugins'][array_search('Database', $plugins)]);
        }

        // Настройка плагина для кеша
        if (in_array('Cache', $plugins) && $bootstrap->hasPluginResource('cache')) {
            $bootstrap->bootstrap('cache');
            $cache = $bootstrap->getResource('cache');
            $options['plugins']['Cache']['backend'] = $cache->getBackend();
            unset($options['plugins'][array_search('Cache', $plugins)]);
        }

        $debug = new ZFDebug_Controller_Plugin_Debug($options);

        $bootstrap->bootstrap('FrontController');
        $frontController = $bootstrap->getResource('FrontController');
        $frontController->registerPlugin($debug);
	}
}