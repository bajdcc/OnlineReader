
<!DOCTYPE html>
<html>
	<head>
		<title>Book</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	</head>

	<body>

	<input type="text" id="new" placeholder="请输入文件名，如1000">
	<input type="button" id="btn" value="加载小说">
	<script type="text/javascript" src="jquery-1.11.1.min.js"></script>
	<script>
		$(document).ready(function(){
			$("#btn").click(function(){
				$.getJSON("getmenu.php?id="+$("#new").val()).error(function(){
					$.getJSON("getmenu.php?id="+$("#new").val(), function(data){
						alert("加载《"+data.title+"》成功！");
						location.reload();
					});
				});
			});
		});
	</script>
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
