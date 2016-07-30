
<!DOCTYPE html>
<html>
	<head>
		<title>Book</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	</head>

	<body>
<?php
if (!file_exists('db.sqlite')) exit;

class MyDB extends SQLite3
{
	function __construct()
	{
		$this->open('db.sqlite');
	}
}

$db = new MyDB();

$results = $db->query("SELECT * FROM BOOK");

echo '<ul>';
while ($row = $results->fetchArray()) {
    $id = $row[1];
    $name = $row[3];
    echo "<li><a href='menu.php?id=$id'>$name</a></li>";
}
echo '</ul>';

$db->close();
?>
	</body>
</html>
