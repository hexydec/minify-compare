<!DOCTYPE html>
<html>
	<head>
		<title>Minify Comparison Tests</title>
		<style>
			html, body {
				margin: 0;
				font-family: Segoe UI;
			}
			body {
				padding: 30px;
			}
			h1 {
				margin: 0 0 20px 0;
			}
			h3 {
				margin: 0;
			}
			.minify__table {
				margin: 0;
				font-size: 0.9em;
				border-spacing: 0;
			}
			.minify__table th, .minify__table td {
				padding: 5px;
				text-align: center;
				border-bottom: 1px solid #CCC;
			}
			.minify__table-start {
				border-left: 2px solid #000;
			}
			.minify__table--best {
				background: green;
				color: #FFF;
			}
			.minify__table--worst {
				background: red;
				color: #FFF;
			}
			.minify__table--failed {
				background: orange;
				color: #FFF;
			}
			.minify__table-totalrow > td {
				border-top: 2px solid #000;
			}
		</style>
	</head>
	<body>
		<h1><?= htmlspecialchars($title); ?></h1>
		<?= $table; ?>
	</body>
</html>
