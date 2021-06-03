<?php

class minifyCompare {

	protected $minifiers = [];
	protected $cache = true;

	public function __construct(array $minifiers, bool $cache = true) {
		$this->minifiers = $minifiers;
		$this->cache = $cache;
		ini_set('memory_limit', '256M');
	}

	public function fetch($url) {
		$cache = dirname(__DIR__).'/cache/'.preg_replace('/[^0-9a-z]++/i', '-', $url).'.cache';
		if ($this->cache && file_exists($cache)) {
			$url = $cache;
		}
		$context = stream_context_create([
			'http' => [
				'headers' => [
					'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
					'Accept-Encoding: none',
					'Accept-Language: en-GB,en;q=0.5',
					'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:87.0) Gecko/20100101 Firefox/87.0'
				]
			]
		]);
		$html = file_get_contents($url, false, $context);
		if ($url != $cache) {
			$dir = dirname($cache);
			if (!is_dir($dir)) {
				mkdir($dir, 0755, true);
			}
			file_put_contents($cache, $html);
		}
		return $html ? $html : false;
	}

	protected function scrapeLinks(string $url, string $selector) {
		$obj = new \hexydec\html\htmldoc();
		if (($html = $this->fetch($url)) !== false && $obj->load($html)) {

			// get the URLs
			$urls = [];
			foreach ($obj->find($selector) AS $item) {
				if (($href = $item->attr('href')) !== null) {
					$urls[] = $href;
				}
				// if (count($urls) == 5) {
				// 	break;
				// }
			}
			return $urls;
		}
		return false;
	}

	protected function minifyUrls(array $urls) {
		$stats = [];
		foreach ($urls AS $url) {

			// fetch the URL
			if (($input = $this->fetch($url)) !== false) {
				$stats[$url] = [
					'input' => strlen($input),
					'inputgzip' => strlen(gzencode($input)),
					'minifiers' => [],
					'best' => [],
					'worst' => []
				];

				// test each minifier
				foreach ($this->minifiers AS $key => $item) {

					// minify
					$start = \microtime(true);
					$output = $this->minify($item, $input, $url);
					$finish = \microtime(true);

					// calculate stats
					$stat = [
						'output' => strlen($output),
						'outputgzip' => $output ? strlen(gzencode($output)) : 0,
						'time' => $finish - $start
					];
					$stat['irregular'] = $stat['output'] < ($stats[$url]['input'] * 0.4);
					$stat['diff'] = $stat['output'] - $stats[$url]['input'];
					$stat['ratio'] = 100 - ((100 / $stats[$url]['input']) * $stat['output']);
					$stat['diffgzip'] = $stat['outputgzip'] - $stats[$url]['inputgzip'];
					$stat['ratiogzip'] = 100 - ((100 / $stats[$url]['inputgzip']) * $stat['outputgzip']);
					$stats[$url]['minifiers'][$key] = $stat;
				}
			}
		}

		// calculate totals
		$totals = [
			'input' => array_sum(array_column($stats, 'input')),
			'inputgzip' => array_sum(array_column($stats, 'inputgzip')),
			'minifiers' => [],
			'best' => [],
			'worst' => []
		];
		foreach (array_keys($this->minifiers) AS $item) {
			$total = [
				'time' => 0,
				'output' => 0,
				'diff' => 0,
				'outputgzip' => 0,
				'diffgzip' => 0,
				'irregular' => false
			];

			// calculate totals
			foreach ($stats AS $url => $data) {
				$total['time'] += $data['minifiers'][$item]['time'];
				$total['output'] += !$data['minifiers'][$item]['irregular'] ? $data['minifiers'][$item]['output'] : $data['input'];
				$total['outputgzip'] += !$data['minifiers'][$item]['irregular'] ? $data['minifiers'][$item]['outputgzip'] : $data['inputgzip'];
				if (!$data['minifiers'][$item]['irregular']) {
					$total['diff'] += $data['minifiers'][$item]['diff'];
					$total['diffgzip'] += $data['minifiers'][$item]['diffgzip'];
				}
			}

			// if there wqs no output, it failed, so make it a ratio of 100%
			$total['ratio'] = 100 - ((100 / $totals['input']) * $total['output']);
			$total['ratiogzip'] = 100 - ((100 / $totals['inputgzip']) * $total['outputgzip']);
			$totals['minifiers'][$item] = $total;
		}
		$stats['Total'] = $totals;

		// work out best and worst
		foreach ($stats AS $url => $stat) {
			$best = [];
			$worst = [];
			$keys = ['time', 'output', 'outputgzip'];
			foreach ($stat['minifiers'] AS $key => $item) {
				foreach ($keys AS $metric) {
					if (!$item['irregular']) { // only check minfiers that produced an output
						if (!isset($best[$metric]) || $item[$metric] < $best[$metric]['value']) {
							$best[$metric] = [
								'metric' => $metric,
								'minifier' => $key,
								'value' => $item[$metric]
							];
						}
						if (!isset($worst[$metric]) || $item[$metric] > $worst[$metric]['value']) {
							$worst[$metric] = [
								'metric' => $metric,
								'minifier' => $key,
								'value' => $item[$metric]
							];
						}
					}
				}
			}
			$stats[$url]['best'] = array_column($best, 'minifier', 'metric');
			$stats[$url]['worst'] = array_column($worst, 'minifier', 'metric');
		}
		return $stats;
	}

	protected function minify(\Closure $minifier, string $input, string $url) {
		\set_time_limit(30);

		// Setup the environment
		$_SERVER['HTTP_HOST'] = parse_url($url, PHP_URL_HOST);
		$_SERVER['REQUEST_URI'] = parse_url($url, PHP_URL_PATH);
		$_SERVER['HTTPS'] = mb_strpos($url, 'https://') === 0 ? 'on' : '';

		// minify
		return call_user_func($minifier, $input, $url);
	}

	public function drawCompareScrape(string $url, string $selector, ?string $title = null) : ?string {
		if (($urls = $this->scrapeLinks($url, $selector)) !== false) {
			return $this->drawCompare($urls, $title);
		}
		return null;
	}

	public function drawCompare(array $urls, ?string $title = null) {
		$keys = array_keys($this->minifiers);
		if (($_GET['action'] ?? '') === 'code' && in_array(($_GET['minifier'] ?? ''), $keys) && in_array(($_GET['url'] ?? ''), $urls)) {
			if (($input = $this->fetch($_GET['url'])) !== false) {
				header('Content-type: text/plain');
				exit($this->minify($this->minifiers[$_GET['minifier']], $input, $_GET['url']));
			}
		} else {
			$stats = $this->minifyUrls($urls);

			// render the table
			$table = $this->compile([
				'minifiers' => $keys,
				'stats' => $stats
			], __DIR__.'/templates/table.php');

			// wrap in a template
			return $this->compile([
				'title' => $title,
				'table' => $table
			], __DIR__.'/templates/template.php');
		}
	}

	/**
	 * Compiles dynamically generated content into a PHP template
	 *
	 * @param array $content An array containing the values to be compiled
	 * @param string $template The absolute/relative (Relative if $prefix) filepath to the template file
	 * @return string The compiled HTML
	 */
	public static function compile(array $content, string $template) : string { // Compiles an Array with a PHP file
		${'template-66f6181bcb4cff4cd38fbc804a036db6'} = $template; // use a weird name to make sure the variable doesn't get overwritten
		foreach ($content AS ${'key-66f6181bcb4cff4cd38fbc804a036db6'} => ${'value-66f6181bcb4cff4cd38fbc804a036db6'}) {
			$${'key-66f6181bcb4cff4cd38fbc804a036db6'} = ${'value-66f6181bcb4cff4cd38fbc804a036db6'}; // manual extract to allow for vars with dashes
		}
		if (is_array($content)) {
			unset($content); // may have been overwritten
		}
		ob_start();
		require(${'template-66f6181bcb4cff4cd38fbc804a036db6'});
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}
}
