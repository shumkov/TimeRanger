<?php

/**
 * @see Zend_Db_Table_Row_Abstract
 */
require_once 'TimeRanger/Storage/Abstract.php';

class TimeRanger_Storage_Redis extends TimeRanger_Storage_Abstract
{
    protected $_table;

    protected $_options = array(
       'dbAdapter'             => null,
       'tableName'             => 'timeranger',
       'applicationNameColumn' => 'application',
       'requestNameColumn'     => 'request',
       'dataColumn'            => 'data',
       'elapsedTimeColumn'     => 'elapsed_time',
       'datetimeColumn'        => 'datetime'
    );

    public function __construct(array $options = array())
    {
        parent::__construct($options);
        
        if (!class_exists('Rediska')) {
            require_once 'TimeRanger/Storage/Exception.php';
            throw new TimeRanger_Storage_Exception(get_class($this) . " adapter require Rediska library");
        }

        $this->_rediska = new Rediska($options);
    }

    public function saveRequest($applicationName, $requestName, $data, $elapsedTime)
    {
        $row = array(
            $this->_options['applicationNameColumn'] => $applicationName,
            $this->_options['requestNameColumn']     => $requestName,
            $this->_options['dataColumn']            => serialize($data),
            $this->_options['elapsedTimeColumn']     => $elapsedTime,
            $this->_options['datetimeColumn']        => new Zend_Db_Expr('NOW()')
        );

        return $this->_table->insert($row);
    }

    public function getApplications()
    {
        $select = $this->_table
                       ->select()
                       ->distinct()
                       ->from($this->_table, $this->_options['applicationNameColumn'])
                       ->order($this->_options['applicationNameColumn']);

        return $this->_table->getAdapter()->fetchCol($select);
    }

    public function getAverageRequests($applicationName)
    {
        $select = $this->_table
                       ->select()
                       ->from($this->_table, array('name' => $this->_options['requestNameColumn'], 'elapsedTime' => new Zend_Db_Expr("AVG({$this->_quoteColumn('elapsedTime')})")))
                       ->where("{$this->_quoteColumn('applicationName')} = ?", $applicationName)
                       ->group($this->_options['requestNameColumn']);

        return $this->_table->getAdapter()->fetchAll($select, array(), Zend_Db::FETCH_OBJ);
    }

    public function getRequests($applicationName, $requestName = null)
    {
        $select = $this->_table
                       ->select()
                       ->from($this->_table, $this->_getColumns())
                       ->where("{$this->_quoteColumn('applicationName')} = ?", $applicationName);

        if (!is_null($requestName)) {
            $select->where("{$this->_quoteColumn('requestName')} = ?", $requestName);
        }

        $requests = $this->_table->getAdapter()->fetchAll($select, array(), Zend_Db::FETCH_OBJ);
        foreach($requests as $request) {
            $request->data = unserialize($request->data);
        }

        return $requests;
    }

    public function getRequest($requestId)
    {
        $select = $this->_table
                       ->select()
                       ->from($this->_table, $this->_getColumns())
                       ->where("id = ?", $requestId);

        $request = $this->_table->getAdapter()->fetchRow($select);

        $request['data'] = unserialize($request['data']);

        return (object)$request;
    }

    public function cleanupRequests($applicationName)
    {
        $where = $this->_table
                      ->getAdapter()
                      ->quoteInto("{$this->_quoteColumn('applicationName')} = ?", $applicationName);

        return $this->_table->delete($where);
    }

    protected function _createTable()
    {
        $sql = "CREATE TABLE `geometria`.`timeranger` (
                    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    {$this->_quoteColumn('applicationName')} VARCHAR(255) NOT NULL,
                    {$this->_quoteColumn('requestName')} VARCHAR(255) NOT NULL,
                    {$this->_quoteColumn('data')} LONGTEXT NOT NULL,
                    {$this->_quoteColumn('elapsedTime')} FLOAT UNSIGNED NOT NULL,
                    {$this->_quoteColumn('datetime')} DATETIME NOT NULL,
                    PRIMARY KEY (`id`),
                    INDEX application({$this->_quoteColumn('applicationName')}),
                    INDEX request({$this->_quoteColumn('requestName')})
                ) CHARACTER SET utf8;";

        $this->_table->getAdapter()->query($sql);
    }

    protected function _quoteColumn($name)
    {
        return $this->_table->getAdapter()->quoteIdentifier($this->_options[$name . 'Column']);
    }

    protected function _getColumns()
    {
        return array(
            'id',
            'application' => $this->_options['applicationNameColumn'],
            'name'        => $this->_options['requestNameColumn'],
            'data'        => $this->_options['dataColumn'],
            'elapsedTime' => $this->_options['elapsedTimeColumn'],
            'datetime'    => $this->_options['datetimeColumn'],
        );
    }
}