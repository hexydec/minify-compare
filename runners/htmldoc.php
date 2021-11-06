<?php
$dir = dirname(__DIR__);
require($dir.'/vendor/autoload.php');
ini_set('memory_limit', '256M');

$minifiers = [
	'HTML Only' => function (string $html, string $url) use ($dir) {
		$obj = new \hexydec\html\htmldoc();
		if ($obj->load($html)) {
			$obj->minify(['style' => false, 'script' => false]);
			return $obj->html();
		}
		return false;
	},
	'HTML + CSS' => function (string $html, string $url) use ($dir) {
		$obj = new \hexydec\html\htmldoc([
			'custom' => [
				'style' => [
					'cache' => null
				]
			]
		]);
		if ($obj->load($html)) {
			$obj->minify(['script' => false]);
			return $obj->html();
		}
		return false;
	},
	'HTML + Javascript' => function (string $html, string $url) use ($dir) {
		$obj = new \hexydec\html\htmldoc([
			'custom' => [
				'script' => [
					'cache' => null
				]
			]
		]);
		if ($obj->load($html)) {
			$obj->minify(['style' => false]);
			return $obj->html();
		}
		return false;
	},
	'HTML + CSS + Javascript' => function (string $html, string $url) use ($dir) {
		$obj = new \hexydec\html\htmldoc([
			'custom' => [
				'style' => [
					'cache' => null
				],
				'script' => [
					'cache' => null
				]
			]
		]);
		if ($obj->load($html)) {
			$obj->minify();
			return $obj->html();
		}
		return false;
	},
	'HTML + CSS + Javascript (Cached)' => function (string $html, string $url) use ($dir) {
		$obj = new \hexydec\html\htmldoc([
			'custom' => [
				'style' => [
					'cache' => $dir.'/cache/%s.css'
				],
				'script' => [
					'cache' => $dir.'/cache/%s.js'
				]
			]
		]);
		if ($obj->load($html)) {
			$obj->minify();
			return $obj->html();
		}
		return false;
	},
];

$urls = [
	// 'https://www.whitehouse.gov/',
	'https://jquery.com/',
	'https://www.plesk.com/',
	'http://www.nationalarchives.gov.uk/',
	'https://www.qualtrics.com',
	'http://dyn.com/',
	'https://www.quantcast.com/',
	'https://www.nginx.com/',
	'http://www.tucows.com/',
	// 'https://www.sonymusic.com/',
	'https://www.nytco.com/',
	'https://group.renault.com/',
	'https://www.maxcdn.com/',
	'http://www.tribunemedia.com/',
	'https://thewaltdisneycompany.com/',
	'https://mindtouch.com/',
	'http://www.company.com/',
	'http://www.siteminder.com',
	'http://www.marinsoftware.com/',
	'http://www.vivendi.com/en/',
	'http://www.boingo.com/',
	'https://adespresso.com/',
	'http://www.toyota.com.br/',
	// 'https://www.loggly.com/',
	'http://www.wolverineworldwide.com/',
	'https://www.sparkpost.com/',
	'https://foliovision.com/',
	'http://www.expressjet.com/',
	'https://modpizza.com/',
	'https://sweden.se/',
	'https://www.wired.com',
	'https://techcrunch.com/',
	'http://fortune.com/',
	'http://www.newyorker.com/',
	'https://news.harvard.edu/gazette/',
	'http://variety.com/',
	'https://www.thesun.co.uk/',
	'http://chicago.suntimes.com/',
	'http://thenextweb.com/',
	'http://www.vogue.com',
	'http://qz.com',
	// 'http://boingboing.net/',
	'http://o.canada.com/',
	'https://9to5mac.com/',
	'http://www.timeinc.com/',
	'http://www.valuewalk.com/',
	'http://crackmagazine.net/',
	//'https://africa.si.edu/', // too many errors - wigs out the validator
	'http://www.washington.edu/',
	// 'http://www.gsu.edu/',
	'http://sprott.carleton.ca',
	'http://www.cooperhewitt.org/',
	// 'https://factsmgt.com/',
	'https://www.lafayette.edu',
	'http://wheatoncollege.edu/',
	'https://www.nicholls.edu/',
	'https://skillcrush.com/',
	'https://www.dmu.edu/',
	'https://www.gemsociety.org/',
	'https://www.polk.edu/',
	'http://www.collegechoice.net/',
	'http://athemes.com/',
	'https://generatepress.com/',
	'http://www.wpexplorer.com',
	'http://www.ripleys.com/',
	// 'http://www.swellbottle.com/',
	// 'https://www.airstream.com/', // too many errors - wigs out the validator
	'https://www.bata.com/',
	'https://www.protest.eu/',
	// 'https://www.etq-amsterdam.com/',
	// 'http://izod.com/',
	// 'https://vanheusen.com/',
	'https://www.fitbark.com/',
	'http://www.tinkeringmonkey.com/',
	'http://www.amc.com/',
	'http://mp3.com/',
	'http://finland.fi/',
	'http://www.bbcamerica.com/',
	'https://www.guildwars2.com',
	'https://www.travelportland.com',
	'http://www.mavs.com/',
	'http://www.no-mans-sky.com/',
	// 'http://www.thepennyhoarder.com/',
	'https://pluto.tv/',
	'http://riverdance.com/',
	'https://www.microsoftstudios.com/',
	'https://www.portent.com/',
	'https://hmn.md/',
	'http://tri.be/',
	'https://10up.com',
	'http://www.designtheplanet.com/',
	'http://www.borngroup.com/',
	'http://waaark.com/',
	'https://creativecommons.org/',
	'https://www.blender.org/',
	'http://www.epi.org/',
	'http://kff.org/',
	'http://invisiblechildren.com/',
	// 'https://cure.org/', // the facebook pixel in the <noscript> tag, causes the validator to assume it is in the body, if the minifier removes the closing head tag, it causes more validator errors, even though the minifier is right
	'https://www.archivesfoundation.org/',
	'http://platformlondon.org/',
	'https://slackhq.com/',
	'https://news.microsoft.com/',
	'https://blog.mozilla.org/',
	'https://blog.cpanel.com/',
	'https://blogs.wsj.com/law/',
	// 'https://www.tripadvisor.com/blog/',
	'https://pulse.target.com/',
	// 'http://blog.staples.ca/',
	'http://blogs.blackberry.com/',
	'https://www.rackspace.com/blog',
	'https://www.bloomberg.com/professional/blog/',
	// 'https://blogs.skype.com/',
	// 'http://blogs.reuters.com/',
	'http://blog.ted.com/',
	// 'https://news.sap.com/', // wayyy too many errors
	'http://newsroom.fb.com/',
	// 'https://longitudes.ups.com/',
	'https://blog.evernote.com',
	'http://blog.us.playstation.com/',
	'http://blog.turbotax.intuit.com',
	'http://starwars.com/news',
	'https://blog.mint.com/',
	'https://blog.alaskaair.com/',
	'http://blog.flickr.net',
	'http://www.shoutmeloud.com/',
	'https://news.spotify.com',
	'http://fourhourworkweek.com/',
	'http://www.beyonce.com/',
	'http://www.rollingstones.com/',
	'http://www.katyperry.com/',
	'http://usainbolt.com/',
	'http://snoopdogg.com/',
	'http://sylvesterstallone.com/',
	'https://www.matthewbarby.com/',
	'http://wilwheaton.net/'
];

$config = [
	'title' => 'HTMLdoc Minification Comparison',
	'cache' => !isset($_GET['nocache'])
];
// $urls = array_slice($urls, 0, 1);
$obj = new \hexydec\minify\compare($minifiers, $urls, $config);
// $url = 'https://kinsta.com/blog/wordpress-site-examples/';
// $selector = 'h3 > a';
// exit($obj->drawPage($url, $selector));
exit($obj->drawPage());
