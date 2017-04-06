<?php

require_once('utils.php');

if (!isset($_GET['id'])) {
	header('HTTP/1.1 404 Not Found');
	header('status: 404 Not Found');
	exit;
}

$id = $_GET['id'];

if (!preg_match('/^\\d+$/', $id)) {
	header('HTTP/1.1 404 Not Found');
	header('status: 404 Not Found');
	exit;
}

$filename = $id . '.txt';

if (!file_exists($filename)) {
	header('HTTP/1.1 404 Not Found');
	header('status: 404 Not Found');
	exit;
}

$bf = file_exists('db.sqlite');

class MyDB extends SQLite3
{
	function __construct()
	{
		$this->open('db.sqlite');
	}
}

header('Content-type: application/json');

$db = new MyDB();

if (!$db) {
	echo $db->lastErrorMsg();
    exit;
}

if (!$bf) {

$sql =
<<<EOF
CREATE TABLE IF NOT EXISTS UID (
    ID INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    MAC            TEXT    NOT NULL,
    PROGRESS       TEXT    NOT NULL,
    DESC           TEXT
);
EOF;

$ret = $db->exec($sql);

if (!$ret) {
	echo $db->lastErrorMsg();
    exit;
}

$sql =
<<<EOF
CREATE TABLE IF NOT EXISTS BOOK (
    ID INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    NAME           INT     NOT NULL,
    INFO           TEXT    NOT NULL,
    DESC           TEXT
);
EOF;

$ret = $db->exec($sql);

if (!$ret) {
	echo $db->lastErrorMsg();
    exit;
}

}

$q = $db->querySingle("SELECT INFO FROM BOOK WHERE NAME=$id");

if ( $q ) {
    $info = $q;
    $objs = json_decode(base64_decode($info), 1);
    
    $ip = getIP();
    $objs['ip'] = $ip;

    $q = $db->querySingle("SELECT PROGRESS FROM UID WHERE MAC='$ip'");

    if ( $q ) {
        $obj = json_decode(base64_decode($q), 1);
        if (isset($obj[$id])) {
            $objs['lastbid'] = $obj[$id]['index'];
        }
    }
    $jsonobj = json_encode($objs, 1);

    echo $jsonobj;
} else {
    $fp = fopen($filename, 'r');
    $title = 'undefined';
    if ( !feof( $fp ) ) {
        $title = trim(fgets($fp, 4096));
    }
    $objs = array('data' => array(), 'title' => $title);
    $i = 0;
    while ( !feof( $fp ) ) {
        $line = fgets($fp, 4096);
        if (preg_match(_filterRegexp(), $line)) {
            $cha = array(
                'begin' => ftell($fp) - strlen($line),
                'name' => $line,
                'id' => $i,
            );
            $objs['data'][] = $cha;
            $i++;
        }
    }
    fclose($fp);
    $jsonobj = json_encode($objs, 1);

    $stmt = $db->prepare('INSERT INTO BOOK VALUES (?,?,?,?)');
    $stmt->bindValue(1, NULL, SQLITE3_NULL);
    $stmt->bindValue(2, intval($id), SQLITE3_INTEGER);
    $stmt->bindValue(3, base64_encode($jsonobj), SQLITE3_TEXT);
    $stmt->bindValue(4, $title, SQLITE3_TEXT);
    $stmt->execute();

    echo '{data:[]}';
}

$db->close();