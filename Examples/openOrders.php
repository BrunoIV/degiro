<?php
	require_once(dirname(__DIR__) . '/DegiroApi.php');

	$api = new DegiroApi();
	$openOrders = $api->getOpenOrders();
	var_dump($openOrders);
