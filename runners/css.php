<?php
$dir = dirname(__DIR__);
require($dir.'/vendor/autoload.php');

$minifiers = [
	'matthiasmullie/minify' => function (string $css) {
		$obj = new \MatthiasMullie\Minify\CSS($css);
		return $obj->minify();
	},
	'tubalmartin/cssmin' => function (string $css) {
		$obj = new tubalmartin\CssMin\Minifier();
		return $obj->run($css);
	},
	'mrclay/minify' => function (string $css) {
		return Minify_CSS_Compressor::process($css);
	},
	'natxet/cssmin' => function (string $css) {
		$obj = new CssMinifier($css);
		return $obj->getMinified();
	},
	'cerdic/css-tidy' => function (string $css) {
		$obj = new csstidy();
		$obj->set_cfg('optimise_shorthands', 2);
		$obj->set_cfg('template', 'high');

		// Parse the CSS
		$obj->parse($css);

		// Get back the optimized CSS Code
		return $obj->print->plain();
	},
	'websharks/css-minifier' => function (string $css) {
		return \WebSharks\CssMinifier\Core::compress($css);
	},
	'wikimedia/minify' => function ($css) {
		return \Wikimedia\Minify\CSSMin::minify($css);
	},
	'taufik-nurrohman' => function (string $css) {
		return minify_css($css);
	},
	'hexydec/cssdoc' => function (string $css) use ($dir) {
		$obj = new \hexydec\css\cssdoc();
		if ($obj->load($css)) {
			$obj->minify();
			return $obj->compile();
		}
		return false;
	},
];

$urls = [
	'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.0.1/css/bootstrap.css',
	'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.css',
	'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.css',
	'https://cdnjs.cloudflare.com/ajax/libs/spectre.css/0.5.9/spectre.css',
	'https://unpkg.com/purecss@2.0.6/build/pure.css',
	'https://cdnjs.cloudflare.com/ajax/libs/bulma/0.9.2/css/bulma.css',
	'https://cdnjs.cloudflare.com/ajax/libs/foundation/6.6.3/css/foundation.css',
	'https://cdnjs.cloudflare.com/ajax/libs/skeleton/2.0.4/skeleton.css',
	'https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.css',
	'https://cdnjs.cloudflare.com/ajax/libs/tachyons/4.11.1/tachyons.css',
	'https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.css',
	'https://cdnjs.cloudflare.com/ajax/libs/uikit/3.6.20/css/uikit-core.css'
];
$config = [
	'title' => 'CSS Minifiers',
	'validator' => function (string $css) {
		if (strlen($css) < 500000) {

			// list of validators we can use
			$validators = ['https://html5.validator.nu/?out=json', 'https://validator.nu/?out=json', 'https://validator.w3.org/nu/?out=json'];
			static $index = 0;

			// create context
			$context = stream_context_create([
				'http' => [
					'header' => [
						'Content-type: text/html; charset=utf-8'
					],
					'user_agent' => 'hexydec/minify-compare',
					'method' => 'POST',
					'content' => $css,
					'timeout' => 10
				]
			]);
			if (($json = file_get_contents($validators[$index], false, $context)) !== false && ($data = json_decode($json, true)) !== null) {

				// compile errors
				$output = [];
				foreach ($data['messages'] AS $item) {
					if ($item['type'] === 'error') {
						$output[] = $item['message'];
					}
				}

				// cycle to the next validator
				if (!isset($validators[++$index])) {
					$index = 0;
				}
				return $output;
			}
		}
		return false;
	},
	'cache' => !isset($_GET['nocache'])
];
$obj = new \hexydec\minify\compare($minifiers, $urls, $config);
exit($obj->drawPage());
