<?php

interface TimeRanger_Storage_Interface
{
	public function __construct(array $options = array());

	public function saveRequest($applicationName, $requestName, $data, $elapsedTime);

	public function getApplications();

	public function getAverageRequests($applicationName);

    public function getRequests($applicationName, $requestName);

    public function getRequest($requestId);

    public function cleanupRequests($applicationName);
}