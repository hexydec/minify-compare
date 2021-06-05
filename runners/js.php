<?php
$dir = dirname(__DIR__);
require($dir.'/vendor/autoload.php');

$minifiers = [
	'hexydec/jslite' => function (string $js) use ($dir) {
		$obj = new \hexydec\jslite\jslite();
		if ($obj->load($js)) {
			$obj->minify();
			return $obj->compile();
		}
		return false;
	},
	'matthiasmullie/minify' => function (string $js) {
		$obj = new \MatthiasMullie\Minify\JS($js);
		return $obj->minify();
	},
	'mrclay/jsmin' => function (string $js) {
		return \JSMin\JSMin::minify($js);
	},
	'tedivm/jshrink' => function (string $js) {
		\set_time_limit(60);
		return \JShrink\Minifier::minify($js);
	},
	'wikimedia/minify' => function ($js) {
		return \Wikimedia\Minify\JavaScriptMinifier::minify($js);
	},
	// 'taufik-nurrohman' => function (string $js) { // crashes
	// 	return minify_js($js);
	// },
];

$urls = [
	'https://github.com/hexydec/dabby/releases/download/0.9.12/dabby.js',
	'https://code.jquery.com/jquery-3.6.0.js',
	'https://cdnjs.cloudflare.com/ajax/libs/vue/3.0.11/vue.runtime.esm-browser.js',
	'https://cdnjs.cloudflare.com/ajax/libs/react/17.0.2/umd/react.development.js',
	'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.0.1/js/bootstrap.esm.js',
	'https://cdnjs.cloudflare.com/ajax/libs/d3/6.7.0/d3.js',
	'https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.js',
	'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.js',
	'https://cdnjs.cloudflare.com/ajax/libs/angular/12.1.0-next.3/core.umd.js',
	'https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.js',
	// 'https://cdnjs.cloudflare.com/ajax/libs/antd/4.16.0/antd.js'
];
$config = [
	'title' => 'Javascript Minifiers',
	'cache' => !isset($_GET['nocache']),
];
$obj = new \hexydec\minify\compare($minifiers, $config);
exit($obj->drawPage($urls));
