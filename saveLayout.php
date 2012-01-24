<?php
	require_once(dirname(__FILE__) . '/config.php');
	require_once(dirname(__FILE__) . '/functions.php');

	$id = 0;
	if (!empty($_REQUEST['type'])) {
		$type = $_REQUEST['type'];
	} else {
		$type = 'json';
	}

	if (!empty($_REQUEST['layout'])) {
		if ($type == 'xml') {
		} else if ($type != 'js' && $type != 'json') {
			die();
		}

		$result = insertLayout($_REQUEST['layout']);
		if ($result !== FALSE) {
			echo $result[1];
		}
	}
?>