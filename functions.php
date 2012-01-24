<?php
	function parseCN($xmlfile, $id = null, $isFile = true) {
		global $__towers, $__classes;
		if ($isFile) {
			$xml = simplexml_load_file($xmlfile);
		} else {
			$xml = simplexml_load_string($xmlfile);
		}

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
		$result['notes'] = mb_check_encoding($result['notes'], 'UTF-8') ? $result['notes'] : utf8_encode($result['notes']);

		if (!empty($defenses[0])) {
			for ($i = 0; $i < count($defenses); $i++) {
				$tower = $__towers[$defenses[$i] - 1];

				$t = array();
				$t['type'] = $tower['name'];
				$t['position'] = array('left' => (float)$positions[$i * 2], 'top' => (float)$positions[($i * 2) + 1]);
				$t['rotation'] = !empty($rotations[$i]) ? (float)$rotations[$i] : 0;
				$t['cost'] = !empty($costs[$i]) ? (int)$units[$defenses[$i] - 1] : -1;
				$t['scale'] = !empty($scales[$i]) ? (float)$scales[$i] : 1;
				if ($t['cost'] == '-1') { unset($t['cost']); }

				$result['classes'][] = $__classes[$tower['class'] - 1]['class'];

				$result['towers'][] = $t;
			}
		}
		$result['classes'] = array_values(array_unique($result['classes']));

		return json_encode($result);
	}

function myErrorHandler($errno, $errstr, $errfile, $errline)
{
    if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting
        return;
    }

    switch ($errno) {
    case E_USER_ERROR:
        echo "<b>My ERROR</b> [$errno] $errstr<br />\n";
        echo "  Fatal error on line $errline in file $errfile";
        echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
        echo "Aborting...<br />\n";
        exit(1);
        break;

    case E_USER_WARNING:
        echo "<b>My WARNING</b> [$errno] $errstr<br />\n";
        break;

    case E_USER_NOTICE:
        echo "<b>My NOTICE</b> [$errno] $errstr<br />\n";
        break;

    default:
        echo "Unknown error type: [$errno - $errfile - $errline] $errstr<br />\n";
        break;
    }

    /* Don't execute PHP internal error handler */
    //return true;
		die();
}
	set_error_handler("myErrorHandler");

	function json2xml($json, $rootElement = 'layout') {
		@include_once("XML/Serializer.php");
		if (class_exists('XML_Serializer')) {
			$options = array('addDecl' => true,
			                 'defaultTagName' => 'item',
			                 'indent' => '    ',
			                 'rootName' => $rootElement
			                );
			$serializer = new XML_Serializer($options);

			$obj = json_decode($json);

			if ($serializer->serialize($obj)) {
				return $serializer->getSerializedData();
			}
		}

		return '<Error message="XML_Serializer not found, unable to output XML." />';
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

	function statement_fetch_assoc($stmt) {
		$row = array();
		$meta = $stmt->result_metadata();
		$params = array();
		while ($field = $meta->fetch_field()) {
			$params[] = &$row[$field->name];
		}

		call_user_func_array(array($stmt, 'bind_result'), $params);

		if ($stmt->fetch()) {
			$result = array();
			foreach ($row as $key => $val) {
				$result[$key] = $val;
			}
			return $result;
		}

		return FALSE;
	}

	function getLayout($publicid, $includeLayoutID = false) {
		$db = getDB();
		$result = null;

		$stmt = $db->prepare('SELECT * from `layouts` as `l` JOIN `towers` as `t` on `t`.`layoutid` = `l`.`layoutid` WHERE `l`.`publicid` = ?');
		$stmt->bind_param("s", $publicid);
		if ($stmt->execute()) {
			while (($row = statement_fetch_assoc($stmt)) !== FALSE) {
				if ($result == null) {
					$result = array('layoutid' => $row['layoutid'],
					                'id' => $row['publicid'],
					                'parent' => $row['parentid'],
					                'level' => $row['level'],
					                'du' => $row['units'],
					                'rating' => $row['rating'],
					                'notes' => $row['notes'],
					                'towers' => array(),
					                'classes' => explode(',', $row['classes']),
					               );

					if ($result['du'] < 0) { unset($result['du']); }
					if ($result['parent'] == null) { unset($result['parent']); }
					if (!$includeLayoutID) { unset($result['layoutid']); }
				}

				$t = array();
				$t['type'] = (string)$row['type'];
				$t['position'] = array('left' => (float)$row['left'], 'top' => (float)$row['top']);
				$t['rotation'] = (float)$row['rotation'];
				$t['cost'] = (int)$row['cost'];
				if ($t['cost'] < 0) { unset($t['cost']); }
				$t['scale'] = (float)$row['scale'];

				$result['towers'][] = $t;
			}
		}

		return $result == null ? '' : json_encode($result);
	}

	function insertLayout($json, $safeDB = true) {
		global $__towerids, $__towers, $__classes;

		$layout = @json_decode($json);
		if (empty($layout)) { return FALSE; }
		$result = true;

		$db = getDB($safeDB);

		$publicid = empty($layout->id) ? 'AA' . base_convert(uniqid(), '16', '36') : (string)$layout->id;
		$owner = empty($layout->owner) ? null : (int)$layout->owner;
		$parent = empty($layout->parent) ? null : (string)$layout->parent;
		$rating = empty($layout->rating) ? 0 : (int)$layout->rating;
		$units = empty($layout->du) ? -1 : (int)$layout->du;
		$level = empty($layout->level) ? 0 : (int)$layout->level;
		$notes = empty($layout->notes) ? "" : (string)$layout->notes;
		$layoutid = -1;

		if (count($layout->towers) == 0 || $level == 0) { return FALSE; }

		$db->autocommit(false);

		$stmt = $db->prepare('INSERT into layouts (`publicid`, `parentid`, `ownerid`, `rating`, `units`, `level`, `notes`) VALUES (?, ?, ?, ?, ?, ?, ?);');
		$stmt->bind_param("ssiiiis", $publicid, $parent, $owner, $rating, $units, $level, $notes);
		if ($stmt->execute()) {
			$layoutid = $stmt->insert_id;
			$error = false;

			$class = array();

			foreach ($layout->towers as $t) {
				$type = (string)$t->type;
				$top = (float)$t->position->top;
				$left = (float)$t->position->left;
				$rotation = empty($t->rotation) ? 0 : (float)$t->rotation;
				$cost = empty($t->cost) ? -1 : (int)$t->cost;
				$scale = empty($t->scale) ? 1 : (float)$t->scale;


				$tower = $__towers[$__towerids[$type]];
				$class[] = $__classes[$tower['class'] - 1]['class'];


				$tstmt = $db->prepare('INSERT into towers (`layoutid`, `type`, `top`, `left`, `rotation`, `cost`, `scale`) VALUES (?, ?, ?, ?, ?, ?, ?);');
				$tstmt->bind_param("isdddid", $layoutid, $type, $top, $left, $rotation, $cost, $scale);
				if (!$tstmt->execute()) {
					$result = false;
				}
			}

			// if ($publicid = '13248') { var_dump($class); }

			$class = implode(',', (array_unique($class)));
			$stmt = $db->prepare('UPDATE layouts set `classes` = ? where `layoutid` = ?');
			$stmt->bind_param("si", $class, $layoutid);
			if (!$stmt->execute()) {
				echo $db->error;
				$result = false;
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

		if ($safeDB) { $db->close(); }

		return $result ? array($layoutid, $publicid) : FALSE;
	}

?>
