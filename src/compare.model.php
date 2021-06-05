<?php
namespace hexydec\minify;

class compareModel {

	public $minifiers = [];
	public $config = [
		'title' => null,
		'cache' => true,
		'ratelimit' => 2000,
		'validator' => null
	];
	public $action = null;
	public $minifier = null;
	public $url = null;

	public function __construct(array $minifiers, array $config) {
		$this->minifiers = $minifiers;
		$this->config = array_merge($this->config, $config);
	}

	public function fetch($url) {
		$cache = dirname(__DIR__).'/cache/'.preg_replace('/[^0-9a-z]++/i', '-', $url).'.cache';
		if ($this->config['cache'] && file_exists($cache)) {
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

	public function scrapeLinks(string $url, string $selector) {
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
					'errors' => $this->validate($input, $validator),
					'validator' => $validator,
					'minifiers' => [],
					'best' => [],
					'worst' => []
				];

				// test each minifier
				foreach ($this->minifiers AS $key => $item) {

					// minify
					$start = \microtime(true);
					$output = $this->minify($item, $input, $url, $error);
					$finish = \microtime(true);

					// calculate stats
					$stat = [
						'error' => $error,
						'output' => strlen($output),
						'outputgzip' => $output ? strlen(gzencode($output)) : 0,
						'time' => $finish - $start,
						'errors' => $this->validate($output, $validator),
						'validator' => $validator
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
		return $stats ? $stats : false;
	}

	public function minify(\Closure $minifier, string $input, string $url, ?string &$error = null) {
		$error = null;
		\set_time_limit(30);

		// Setup the environment
		$_SERVER['HTTP_HOST'] = parse_url($url, PHP_URL_HOST);
		$_SERVER['REQUEST_URI'] = parse_url($url, PHP_URL_PATH);
		$_SERVER['HTTPS'] = mb_strpos($url, 'https://') === 0 ? 'on' : '';

		// minify
		try {
			return call_user_func($minifier, $input, $url);
		} catch (Throwable $e) {
			$error = $e->getMessage();
			return false;
		}
	}

	protected function validate(string $code, ?array &$output = null) {
		if (!empty($this->config['validator'])) {

			// pull from cache
			$cache = $this->config['cache'] ? dirname(__DIR__).'/cache/'.md5($code).'.json' : null;
			if (file_exists($cache) && ($json = file_get_contents($cache)) !== false && ($data = json_decode($json, true)) !== null) {
				$output = $data['output'];
				return $data['errors'];
			} else {

				// rate limit
				static $last = 0;
				$now = microtime(true);
				if ($last + $this->config['ratelimit'] > $now) {
					usleep(($this->config['ratelimit'] - ($now - $last)) * 1000);
				}
				$last = microtime(true);

				// run the validator
				if (($errors = $this->config['validator']($code, $output)) !== false) {

					// save to cache
					if ($cache) {
						file_put_contents($cache, json_encode([
							'errors' => $errors,
							'output' => $output
						]));
					}
					return $errors;
				}
			}
		}
		return null;
	}

	public function getTotals(array $stats) {
		$totals = [
			'input' => array_sum(array_column($stats, 'input')),
			'inputgzip' => array_sum(array_column($stats, 'inputgzip')),
			'minifiers' => [],
			'errors' => 0,
			'validator' => null,
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
				'irregular' => false,
				'errors' => 0,
				'validator' => null
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
		return $totals;
	}

	public function getBestAndWorst(array $stats) {
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

	public function getMinifyStats(array $urls) {
		if (($stats = $this->minifyUrls($urls)) !== false) {
			$stats['Total'] = $this->getTotals($stats);
			return $this->getBestAndWorst($stats);
		}
		return false;
	}
}
