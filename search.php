<?php
	require_once(dirname(__FILE__) . '/config.php');
	require_once(dirname(__FILE__) . '/functions.php');

	/**
	 * Add a given parameter into the parameters to be passed to bind_param
	 * when executing the query.
	 */
	function addParam($value, $type) {
		global $__paramtypes, $__params;

		if (empty($__params)) {
			$__params = array();
			$__paramtypes = array();
		}

		$__paramtypes[] = $type;
		$__params[] = $value;

	}
	/**
	 * Get an array to pass via call_user_func_array to bind_param.
	 * This creates a bunch of uniquely-named variables containig the values
	 * that have been added, and then the binding actually happens against
	 * these randomly named variables.
	 *
	 * Its not the nicest way of doing this, but I don't think there is a
	 * nicer way to do a dynamic prepared statement.
	 */
	function getParams() {
		global $__paramtypes, $__params;
		$p = array();
		foreach ($__params as $param) {
			$varname = uniqid('param');
			$$varname = $param;
			$p[] = &$$varname;
		}
		array_unshift($p, implode('', $__paramtypes));
		return $p;
	}

	$searchData = !empty($_REQUEST['search']) ? $_REQUEST['search'] : '';
	$searchData = json_decode($searchData);
	if (empty($searchData)) {
		$searchData = array();
		if (isset($_REQUEST['map'])) { $searchData['map'] = explode(',', $_REQUEST['map']); }
		if (isset($_REQUEST['classes'])) { $searchData['classes'] = explode(',', $_REQUEST['classes']); }
		if (isset($_REQUEST['limit'])) { $searchData['limit'] = $_REQUEST['limit']; }
		if (isset($_REQUEST['units'])) { $searchData['units'] = $_REQUEST['units']; }
		if (isset($_REQUEST['notes'])) { $searchData['notes'] = $_REQUEST['notes']; }
		if (isset($_REQUEST['rating'])) { $searchData['rating'] = $_REQUEST['rating']; }

		//TODO: Difficulty, Type, Mode

		// Turn into a stdClass to use below...
		$searchData = json_decode(json_encode($searchData));
	}

	$query = 'SELECT * FROM layouts';
	$where = array();

	if (!empty($searchData->map)) {
		$mapwhere = array();
		$maps = $searchData->map;
		$maps = !is_array($maps) ? array($maps) : $maps;
		foreach ($maps as $level) {
			$mapwhere[] = 'level = ?';
			addParam($level, 'i');
		}

		if (count($mapwhere) > 0) {
			$where[] = '(' . implode(' OR ', $mapwhere) . ')';
		}
	}

	if (!empty($searchData->units)) {
		$units = $searchData->units;
		$where[] = 'level <= ?';
		addParam($units, 'i');
	}

	if (!empty($searchData->notes)) {
		$notes = $searchData->notes;
		$where[] = 'notes LIKE ?';
		addParam($notes, 's');
	}

	$classes = array();
	foreach ($__classes as $c) { $classes[$c['class']] = false; }

	if (!empty($searchData->classes)) {
		foreach ($searchData->classes as $class) {
			if (isset($classes[$class])) {
				$classes[$class] = true;
			}
		}

		foreach ($classes as $c => $allowed) {
			if (!$allowed) {
				$where[] = ' NOT FIND_IN_SET(?, classes) ';
				addParam($c, 's');
			}
		}
	}

	if (!empty($where)) {
		$query = $query . ' WHERE ' . implode(' AND ', $where);
	}

	$limit = empty($searchData->limit) ? 10 : min((int)$searchData->limit, 10000);

	$query .= ' ORDER BY layoutid DESC ';
	$query .= ' LIMIT ? ';
	addParam($limit, 'i');

	$db = getDB();
	$stmt = $db->prepare($query);
	call_user_func_array(array($stmt, 'bind_param'), getParams());
	$stmt->execute();
	$result = array();

	while (($row = statement_fetch_assoc($stmt)) !== FALSE) {
		$res = array('id' => $row['publicid'],
		             'notes' => $row['notes'],
		             'classes' => $row['classes'],
		             'units' => $row['units'],
		             'rating' => $row['rating'],
		             'level' => $row['level'],
		             'difficulty' => $row['difficulty'],
		             'type' => $row['type'],
		             'mode' => $row['mode'],
		             );
		if ($res['units'] < 0) { unset($res['units']); }
		$result[] = $res;
	}

	header("content-type: application/json");
	echo json_encode($result);
?>
