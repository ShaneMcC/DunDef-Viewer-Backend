<?php
	function xml2json($xmlfile, $id = null) {
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
		if ($id != null) { $result['id'] = $id; }

		ob_start();
		for ($i = 0; $i < count($defenses); $i++) {
			$tower = $towers[$defenses[$i] - 1];

			$t = array();
			$t['type'] = $tower['name'];
			$t['position'] = array('left' => (float)$positions[$i * 2], 'top' => (float)$positions[($i * 2) + 1]);
			$t['rotation'] = (float)$rotations[$i];
			$t['cost'] = $units[$defenses[$i]];
			$t['scale'] = !empty($scales[$i]) ? $scales[$i] : 1;

			$result['classes'][] = $classes[$tower['class']]['class'];

			$result['towers'][] = $t;
		}

		$result['classes'] = array_values(array_unique($result['classes']));

		return json_encode($result);
	}


	function getDB($new = false) {
		global $host, $user, $pass, $database, $mysqli;

		if ($new || empty($mysqli)) {
			$obj = new mysqli($host, $user, $pass, $database);

			if ($new) {
				return $obj;
			}
			$mysqli = $obj;
		}

		return $mysqli;
	}

	function insertLayout($json) {
		$layout = json_decode($json);
		$result = true;

		$db = getDB(true);

		$publicid = empty($layout->id) ? 'AA' . base_convert(uniqid(), '16', '36') : (string)$layout->id;
		$owner = empty($layout->owner) ? null : (int)$layout->owner;
		$parent = empty($layout->parent) ? null : (string)$layout->parent;
		$rating = empty($layout->rating) ? 0 : (int)$layout->rating;
		$units = empty($layout->du) ? -1 : (int)$layout->du;
		$level = empty($layout->level) ? 0 : (int)$layout->level;
		$classes = empty($layout->classes) ? '' : implode(',', $layout->classes);

		$db->autocommit(false);

		$stmt = $db->prepare('INSERT into layouts (`publicid`, `parentid`, `ownerid`, `rating`, `units`, `level`, `classes`) VALUES (?, ?, ?, ?, ?, ?, ?);');
		$stmt->bind_param("ssiiiis", $publicid, $parent, $owner, $rating, $units, $level, $classes);
		if ($stmt->execute()) {
			$layoutid = $stmt->insert_id;
			$error = false;

			foreach ($layout->towers as $t) {
				$type = (string)$t->type;
				$top = (float)$t->position->top;
				$left = (float)$t->position->left;
				$rotation = (float)$t->rotation;
				$cost = (int)$t->cost;
				$scale = (float)$t->scale;

				$tstmt = $db->prepare('INSERT into towers (`layoutid`, `type`, `top`, `left`, `rotation`, `cost`, `scale`) VALUES (?, ?, ?, ?, ?, ?, ?);');
				$tstmt->bind_param("isdddid", $layoutid, $type, $top, $left, $rotation, $cost, $scale);
				if (!$tstmt->execute()) {
					$result = false;
				}
			}

			if ($result) {
				$db->commit();
			} else {
				$db->rollback();
			}
		} else {
			$result = false;
		}

		if (!$result) { echo $db->error; }

		$db->autocommit(true);
		$db->close();

		return $result;
	}

?>
