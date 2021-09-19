<?php
	require_once(dirname(__DIR__) . '/DegiroApi.php');

	$api = new DegiroApi();
	$favs = $api->getFavoritesIds();
	var_dump($favs);
