<?php
$dir = dirname(__DIR__);
require($dir.'/vendor/autoload.php');

ini_set('memory_limit', '256M');

$minifiers = [
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
	'hexydec/jslite' => function (string $js) use ($dir) {
		$obj = new \hexydec\jslite\jslite();
		if ($obj->load($js)) {
			$obj->minify();
			return $obj->compile();
		}
		return false;
	},
];

$urls = [
	'https://github.com/hexydec/dabby/releases/download/v0.9.14/dabby.js',
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
	'validator' => function (string $js) {
		$cmd = 'node "'.__DIR__.'/esprima.js"';

		// https://bugs.php.net/bug.php?id=49139
		if (PHP_OS == 'WINNT' && PHP_MAJOR_VERSION  < 8) {
			$cmd = '"'.$cmd.'"';
		}

		// setup pipes
		$descriptors = [
			0 => ['pipe', 'rb'],  // stdin
			1 => ['pipe', 'wb'],  // stdout
			2 => ['pipe', 'w'],  // stderr
		];

		// run command
		if (($proc = proc_open($cmd, $descriptors, $pipes)) !== false && is_resource($proc)) {

			// send input to stdin
			fwrite($pipes[0], $js);
			fclose($pipes[0]);

			// retrieve outputted PDF
			$result = stream_get_contents($pipes[1]);
			fclose($pipes[1]);

			// retrieve program output
			$error = stream_get_contents($pipes[2]);
			fclose($pipes[2]);

			// get the status
			$status = proc_get_status($proc);

			// close connection
			proc_close($proc);

			$output = [];
			if ($result && ($json = json_decode($result, true)) !== null) {
				foreach ($json AS $item) {
					$output[] = 'Line: '.$item['lineNumber'].', Index: '.$item['index'].' - '.$item['description'];//.' ('.substr($js, $item['index'] - 100, 200).')';
				}
			}
			// var_dump($output, $result, $error, $status);
			// exit();
			return $output;
		}
		return null;
	},
	'cache' => !isset($_GET['nocache'])
];
$obj = new \hexydec\minify\compare($minifiers, $urls, $config);
exit($obj->drawPage());
