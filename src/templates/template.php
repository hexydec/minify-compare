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
			.icon-code {
				width: 16px;
				height: 16px;
				display: block;
			}
			.icon-code::before {
				content: url(data:image/svg+xml;base64,<?= base64_encode('<svg viewBox="0 0 40 32"><path d="M26 23l3 3 10-10-10-10-3 3 7 7z"></path><path d="M14 9l-3-3-10 10 10 10 3-3-7-7z"></path><path d="M21.916 4.704l2.171 0.592-6 22.001-2.171-0.592 6-22.001z"></path></svg>'); ?>);
				width: 16px;
				height: 16px;
				display: block;
			}
		</style>
	</head>
	<body>
		<h1><?= htmlspecialchars($title); ?></h1>
		<?= $table; ?>
	</body>
</html>
