<?php

class TimeInspector_Application_Resource_Storage extends Zend_Application_Resource_ResourceAbstract
{
    public function init()
    {
        $options = $this->getOptions();

        if (isset($options['name'])) {
            $name = $options['name'];
            unset($options['name']);
        } else {
            $name = 'db';
        }

        return TimeRanger_Storage_Abstract::factory(
            $name,
            $options
        );
    }
}
