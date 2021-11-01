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
			.minify__popup-switch {
				display: none;
			}
			.minify__popup-label {
				display: block;
				width: 20px;
				height: 20px;
				cursor: pointer;
			}
			.minify__popup {
				opacity: 0;
				visibility: hidden;
				transition: all 0.3s;
				position: fixed;
				top: 0;
				right: 0;
				bottom: 0;
				left: 0;
				display: flex;
				justify-content: center;
				align-items: center;
				z-index: 10;
				text-align: left;
				background: rgba(0,0,0,0.5);
			}
			.minify__popup-switch:checked ~ .minify__popup {
				opacity: 1;
				visibility: visible;
			}
			.minify__popup-inner {
				max-width: 80vw;
				max-height: 90vh;
				background: #FFF;
				padding: 15px;
				position: relative;
				display: flex;
				flex-direction: column;
			}
			.minify__popup-close {
				position: absolute;
				top: 10px;
				right: 10px;
				width: 30px;
				height: 30px;
				text-indent: 30px;
				overflow: hidden;
				cursor: pointer;
			}
			.minify__popup-output {
				border: 1px solid #AAA;
				padding: 15px 15px 15px 35px;
				overflow: auto;
				flex: 1 1 auto;
				margin: 0;
			}
			.minify__popup-output-item--error {
				color: red;
			}
			.minify__popup-heading {
				margin: 0;
			}
			.minify__popup-subheading {
				margin: 0;
				display: flex;
				justify-content: space-between;
			}
			.minify__popup-code {
				width: 30px;
				height: 24px;
			}

			.icon-cross {
				background: url(data:image/svg+xml;base64,<?= base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="red"><path d="M17.9 16l7-7c.6-.6.6-1.4 0-2s-1.3-.5-1.8 0L16 14.2l-7-7c-.6-.6-1.4-.6-2 0s-.5 1.3 0 1.8l7.1 7.1-7 7c-.6.6-.6 1.4 0 2 .2.2.5.3.9.3s.7-.1 1-.4l7-7 7 7a1.4 1.4 0 001.9 0c.6-.5.6-1.3 0-1.8L18 16z"/></svg>'); ?>);
				background-size: contain;
			}
			.icon-tick {
				background: url(data:image/svg+xml;base64,<?= base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="green"><path d="M27.6 7c-.5-.5-1.3-.5-1.9 0L12 20.9l-5.7-5.7c-.6-.6-1.4-.6-1.9 0s-.5 1.3 0 1.8l6.7 6.7c.2.3.5.4.9.4s.7-.1 1-.4L27.5 8.9c.5-.5.5-1.3 0-1.8z"/></svg>'); ?>);
				background-size: contain;
			}
			.icon-code {
				background: url(data:image/svg+xml;base64,<?= base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="40" height="32"><path d="M26 23l3 3 10-10-10-10-3 3 7 7z"></path><path d="M14 9l-3-3-10 10 10 10 3-3-7-7z"></path><path d="M21.916 4.704l2.171 0.592-6 22.001-2.171-0.592 6-22.001z"></path></svg>'); ?>);
				background-size: contain;
			}

			:root {
				--progress: 0%;
			}

			.minify__progress {
				opacity: 0;
				visibility: hidden;
				transition: all 0.3s;
				position: fixed;
				bottom: 20px;
				right: 20px;
				padding: 10px;
				box-shadow: 0 0 5px #CCC;
				background: linear-gradient(90deg, #afedaf var(--progress), #FFF var(--progress));
				width: 300px;
				font-size: 1.1em;
			}

			.minify__progress--running {
				opacity: 1;
				visibility: visible;
			}
		</style>
	</head>
	<body>
		<h1><?= htmlspecialchars($title); ?></h1>
		<?= $table; ?>
	</body>
</html>
