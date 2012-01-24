<?php
	require_once(dirname(__FILE__) . '/config.php');
	require_once(dirname(__FILE__) . '/functions.php');


	$id = '13934';
	$xmlfile = $xmldir . $id . '.xml';

	insertLayout(xml2json($xmlfile, $id));
?>
