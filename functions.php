<?php
	function xml2json($xmlfile) {
		global $towers, $classes;
		$xml = simplexml_load_file($xmlfile);

		$positions = explode(',', $xml->Layout->Positions);
		$defenses = explode(',', $xml->Layout->Defenses);
		$rotations = explode(',', $xml->Layout->Rotations);
		$units = explode(',', $xml->Layout->Costs);
		$scales = explode(',', $xml->Layout->Scales);

		$notes = urldecode($xml->Layout->Notes);
		$notes = json_encode($notes);

		$level = $xml->Layout->Map;
		$leveldu = $xml->Layout->DU;
		$rating = $xml->Layout->Rating;

		$classes = array();

		ob_start();
		echo 'towers: [', "\n";
		for ($i = 0; $i < count($defenses); $i++) {
			$left = $positions[$i * 2];
			$top = $positions[($i * 2) + 1];
			$rotation = $rotations[$i];
			$tower = $towers[$defenses[$i] - 1];
			$type = $tower['name'];
			$classes[] = $tower['class'];
			$du = $units[$defenses[$i]];

			$scale = !empty($scales[$i]) ? $scales[$i] : 1;

			printf("    {type: '%s', position: {left: %.13g, top: %.13g}, rotation: %.13g, cost: %d, scale: %.13g},\n", $type, $left, $top, $rotation, $du, $scale);
		}
		echo '  ]', "\n";
		$towers = ob_get_contents();
		ob_end_clean();

		$classes = array_unique($classes);
		$classes = '[' . implode(',', $classes) . ']';

		ob_start();
	?>
var layout = {
  level: <?=$level; ?>,
  du: <?=$leveldu; ?>,
  rating: <?=$rating; ?>,
  notes: <?=$notes; ?>,

  <?=$towers; ?>,

  classes: <?=$classes; ?>,
}
<?php
		$result = ob_get_contents();
		ob_end_clean();
		return $result;
	}

?>