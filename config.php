<?php
	// Database Details
	$host = 'localhost';
	$user = 'dundef';
	$pass = '';
	$database = 'dundef';

	// Location of XML Files (Temporary)
	$xmldir = '/home/shane/dd/';

	if (file_exists(dirname(__FILE__) . '/config.local.php')) {
		require_once(dirname(__FILE__) . '/config.local.php');
	}


	$classes = array(array('name' => 'Apprentice / Adept', 'class' => 'apprentice'),
	                 array('name' => 'Squire / Countess', 'class' => 'squire'),
	                 array('name' => 'Monk / Initiate', 'class' => 'monk'),
	                 array('name' => 'Huntress / Ranger', 'class' => 'huntress'),
	                 );

	$towers = array(array('name' => 'ensnare', 'class' => '3'),
	                array('name' => 'electric', 'class' => '3'),
	                array('name' => 'enrage', 'class' => '3'),
	                array('name' => 'healing', 'class' => '3'),
	                array('name' => 'drain', 'class' => '3'),
	                array('name' => 'blockade', 'class' => '1'),
	                array('name' => 'missile', 'class' => '1'),
	                array('name' => 'fireball', 'class' => '1'),
	                array('name' => 'lightning', 'class' => '1'),
	                array('name' => 'striker', 'class' => '1'),
	                array('name' => 'spike', 'class' => '2'),
	                array('name' => 'bouncer', 'class' => '2'),
	                array('name' => 'harpoon', 'class' => '2'),
	                array('name' => 'bowling', 'class' => '2'),
	                array('name' => 'slice', 'class' => '2'),
	                array('name' => 'gas', 'class' => '4'),
	                array('name' => 'mine', 'class' => '4'),
	                array('name' => 'inferno', 'class' => '4'),
	                array('name' => 'etheral', 'class' => '4'),
	                array('name' => 'darkness', 'class' => '4'),
	               );
?>
