<?php
	function xml2json($xmlfile) {
		global $towers, $classes;
		$xml = simplexml_load_file($xmlfile);

		$positions = explode(',', $xml->Layout->Positions);
		$defenses = explode(',', $xml->Layout->Defenses);
		$rotations = explode(',', $xml->Layout->Rotations);
		$units = explode(',', $xml->Layout->Costs);
		$scales = explode(',', $xml->Layout->Scales);

		$result = array('level' => (int)$xml->Layout->Map,
		                'du' => (int)$xml->Layout->DU,
		                'rating' => (int)$xml->Layout->Rating,
		                'notes' => (string)urldecode($xml->Layout->Notes),
		                'towers' => array(),
		                'classes' => array(),
		               );
		if ($result['du'] < 0) { unset($result['du']); }

		ob_start();
		for ($i = 0; $i < count($defenses); $i++) {
			$tower = $towers[$defenses[$i] - 1];

			$t = array();
			$t['type'] = $tower['name'];
			$t['position'] = array('left' => (float)$positions[$i * 2], 'top' => (float)$positions[($i * 2) + 1]);
			$t['rotation'] = (float)$rotations[$i];
			$t['cost'] = $units[$defenses[$i]];
			$t['scale'] = !empty($scales[$i]) ? $scales[$i] : 1;

			$result['classes'][] = $tower['class'];

			$result['towers'][] = $t;
		}

		$result['classes'] = array_values(array_unique($result['classes']));

		return 'var layout = ' . json_encode($result);
	}

?>
