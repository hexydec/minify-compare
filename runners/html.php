<?php
$dir = dirname(__DIR__);
require($dir.'/vendor/autoload.php');
require($dir.'/vendor/taufik-nurrohman/php-html-css-js-minify/php-html-css-js-minifier.php');

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

$obj = new minifyCompare($minifiers, !isset($_GET['nocache']));
$url = 'https://kinsta.com/blog/wordpress-site-examples/';
$selector = 'h3 > a';
if (($html = $obj->drawCompareScrape($url, $selector, 'HTML Minifiers')) !== null) {
	echo $html;
}
