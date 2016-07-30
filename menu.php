
<!DOCTYPE html>
<html>
	<head>
		<title>Menu</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<link rel="stylesheet" type="text/css" href="jquery.dataTables.min.css">
		<script type="text/javascript" src="jquery-1.11.1.min.js"></script>
		<script type="text/javascript" src="jquery.dataTables.min.js"></script>
	</head>

	<body>
		<script>
			function gup(name) {
				name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
				var regexS = "[\\?&]" + name + "=([^&#]*)";
				var regex = new RegExp(regexS);
				var results = regex.exec(location.search);
				if (results === null) {
					return null;
				}
				else {
					return results[1];
				}
			}
			(function($){$(function(){
				var id=gup('id');
				if (!id) return;
				$('#example').dataTable( {
					ajax: 'getmenu.php?id='+id,
					deferRender: true,
					paging: true,
					autoWidth: true,
					columns: [
						{ title: "Id", data: 'id', searchable: true, type: "numeric", width: "50px" },
						{ title: "Name", data: 'name', searchable: true, width: "500px" },
						{ title: "Read", data: 'begin', searchable: false, width: "100px", 
							render: function ( data, type, full, meta ) {
								return '<a href="reading.php?force=1&id='+id+'&bid='+(full.id)+'">Reading Now!</a>';
							}
						},
					],
					language: {
						url: "Chinese.json"
					},
				});
			})})(jQuery);
		</script>
		<table id="example" class="display" cellspacing="0" width="100%"></table>
	</body>
</html>
