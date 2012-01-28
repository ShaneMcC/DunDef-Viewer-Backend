<?php
	require_once(dirname(__FILE__) . '/config.php');
	require_once(dirname(__FILE__) . '/functions.php');

	$id = 0;
	if (!empty($_REQUEST['id'])) {
		$id = $_REQUEST['id'];
	} else if (!empty($argv[1])) {
		$id = $argv[1];
	}
	if (!preg_match('#^[0-9A-Za-z]+$#', $id)) {
		die();
	}

	$json = getLayout($id);

	if (isset($_REQUEST['type']) && $_REQUEST['type'] == 'xml') {
		header("content-type: text/xml");
		echo json2xml($json, 'layout');
		return;
	}

	header("content-type: application/x-javascript");
	echo $json;
?>
