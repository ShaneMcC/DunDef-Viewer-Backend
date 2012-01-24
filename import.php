<?php
	require_once(dirname(__FILE__) . '/config.php');
	require_once(dirname(__FILE__) . '/functions.php');

	$files = glob($xmldir . '/*.xml');
	natsort($files);

	// $db = getDB();
	// $db->query('TRUNCATE TABLE `layouts`');

	foreach ($files as $file) {
		if (preg_match('#^.*/([0-9]+)\.xml$#', $file, $m)) {
			$id = $m[1];

			// if ($id < 13000 || $id > 13250) { continue; }

			$json = getLayout($id);
			if (empty($json)) {
				echo 'Importing ', $id, '... ';
				$json = parseCN($file, $id);
				insertLayout($json, true, true);
				echo 'Done!', "\n";
			}
		}
	}
?>
