<?php

require_once(__DIR__ .'/utils.php');

function get_param_num($str) {

	if (!isset($_GET[$str])) {
		header('HTTP/1.1 404 Not Found');
		header('status: 404 Not Found');
		exit;
	}

	$num = $_GET[$str];

	if (!preg_match('/^\\d+$/', $num)) {
		header('HTTP/1.1 404 Not Found');
		header('status: 404 Not Found');
		exit;
	}

	return $num;
}

$id = get_param_num('id');
$start = intval(get_param_num('start'));
$end = isset($_GET['end']) ? intval(get_param_num('end')) : 0;
$filename = $id . '.txt';

if (!file_exists($filename)) {
	header('HTTP/1.1 404 Not Found');
	header('status: 404 Not Found');
	exit;
}

header('X-Server: bajdcc_reader_system');

$fp = fopen($filename, "r");
$len = filesize($filename);
$end = $end == 0 ? $len : $end;

$limit_len = 200000;
$l = $end - $start;

if ($start < 0 || $end < 0 || $start >= $end || $start > $len || $end > $len || $l > $limit_len) {
	header('HTTP/1.1 404 Not Found');
	header('status: 404 Not Found');
	exit;
}

if (_addEtag()) {

	fseek($fp, $start);
	$str = fread($fp, $l);
	fclose($fp);

	echo $str;
}

if (!isset($_GET['bid'])) {
	exit;
}

$bid = $_GET['bid'];

if (!preg_match('/^\\d+$/', $bid)) {
	header('HTTP/1.1 404 Not Found');
	header('status: 404 Not Found');
	exit;
}

if (!file_exists('db.sqlite')) {
	exit;
}

class MyDB extends SQLite3
{
	function __construct()
	{
		$this->open('db.sqlite');
	}
}

$db = new MyDB();

if (!$db) {
	echo $db->lastErrorMsg();
    exit;
}

$ip = getIP();
$q = $db->querySingle("SELECT PROGRESS FROM UID WHERE MAC='$ip'");

if ( $q ) {
    $objs = json_decode(base64_decode($q), 1);
    if (!isset($objs[$id])) {
		$objs[$id] = array('index' => $bid);
	} else if ($objs[$id]['index'] == $bid) {
		$db->close();
		exit;
	} else {
		$objs[$id]['index'] = $bid;
	}
	$jsonobj = json_encode($objs, 1);

	$stmt = $db->prepare('UPDATE UID SET PROGRESS=?, DESC=? WHERE MAC=?');
    $stmt->bindValue(1, base64_encode($jsonobj), SQLITE3_TEXT);
	$stmt->bindValue(2, strval(time()), SQLITE3_TEXT);
	$stmt->bindValue(3, $ip, SQLITE3_TEXT);
    $stmt->execute();
} else {
    $objs = array();
    $objs[$bid] = array('index' => $bid);
    $jsonobj = json_encode($objs, 1);

    $stmt = $db->prepare('INSERT INTO UID VALUES (?,?,?,?)');
    $stmt->bindValue(1, NULL, SQLITE3_NULL);
    $stmt->bindValue(2, $ip, SQLITE3_TEXT);
    $stmt->bindValue(3, base64_encode($jsonobj), SQLITE3_TEXT);
    $stmt->bindValue(4, strval(time()), SQLITE3_TEXT); //date('Y-m-d H:i:s')
    $stmt->execute();
}

$db->close();