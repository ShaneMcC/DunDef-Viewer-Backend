<?php
	require_once(dirname(__FILE__) . '/config.php');
	require_once(dirname(__FILE__) . '/functions.php');

	$files = glob($xmldir . '/*.xml');
	natsort($files);

	foreach ($files as $file) {
		if (preg_match('#^.*/([0-9]+)\.xml$#', $file, $m)) {
			$id = $m[1];
			$json = getLayout($id);
			if (empty($json)) {
				echo 'Importing ', $id, '... ';
				$json = parseCN($file, $id);
				insertLayout($json);
				echo 'Done!', "\n";
			}
		}
	}
?>
