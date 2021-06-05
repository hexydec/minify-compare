<?php
$dir = dirname(__DIR__);
require($dir.'/vendor/autoload.php');

$minifiers = [
	'hexydec/htmldoc' => function (string $html, string $url) use ($dir) {
		$obj = new \hexydec\html\htmldoc([
			'custom' => [
				'style' => [
					'cache' => $dir.'/cache/%s.css',
					// 'minifier' => null
				],
				'script' => [
					'cache' => $dir.'/cache/%s.js',
					// 'minifier' => null
				]
			]
		]);
		if ($obj->load($html)) {
			$obj->minify();
			return $obj->html();
		}
		return false;
	},
	'voku/html-min' => function (string $html) {
		$htmlMin = new \voku\helper\HtmlMin();
		// if (strpos($html, 'Tucows')) {
		// 	exit($htmlMin->minify($html));
		// }
		return $htmlMin->minify($html);
	},
	'mrclay/minify' => function (string $html) {
		return Minify_HTML::minify($html);
	},
	'taufik-nurrohman' => function (string $html) {
		return minify_html($html);
	},
	// 'pfaciana/tiny-html-minifier' => function (string $html) { // incorrect
	// 	return \Minifier\TinyMinify::html($html);
	// },
	// 'deruli/html-minifier' => function (string $html) { // so slow
	// 	$obj = new \zz\Html\HTMLMinify($html);
	// 	return $obj->process();
	// }
];

$config = [
	'title' => 'HTML Minifiers',
	'cache' => !isset($_GET['nocache']),
	'validator' => function (string $html, ?array &$output = null) {
		$url = 'https://html5.validator.nu/?out=json';
		$context = stream_context_create([
			'http' => [
				'header' => [
					'Content-type: text/html; charset=utf-8'
				],
				'user_agent' => 'hexydec/minify-compare',
				'method' => 'POST',
				'content' => $html
			]
		]);
		if (($json = file_get_contents($url, false, $context)) !== false && ($data = json_decode($json, true)) !== null) {
			$output = [];
			foreach ($data['messages'] AS $item) {
				if ($item['type'] === 'error') {
					$output[] = $item['message'];
				}
			}
			return count($output);
		}
		return false;
	}
];
$obj = new \hexydec\minify\compare($minifiers, $config);
$url = 'https://kinsta.com/blog/wordpress-site-examples/';
$selector = 'h3 > a';
exit($obj->drawPage($url, $selector));
